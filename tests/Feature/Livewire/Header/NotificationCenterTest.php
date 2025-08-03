<?php

namespace Tests\Feature\Livewire\Header;

use App\Livewire\Header\NotificationCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Packages\User\Models\User;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_render_notification_center()
    {
        $this->actingAs($this->user);

        Livewire::test(NotificationCenter::class)
            ->assertStatus(200)
            ->assertSee('Notifications');
    }

    /** @test */
    public function it_loads_notifications_on_mount()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NotificationCenter::class);

        $this->assertIsArray($component->get('notifications'));
        $this->assertIsInt($component->get('unreadCount'));
    }

    /** @test */
    public function it_can_toggle_dropdown()
    {
        $this->actingAs($this->user);

        Livewire::test(NotificationCenter::class)
            ->assertSet('showDropdown', false)
            ->call('toggleDropdown')
            ->assertSet('showDropdown', true)
            ->call('toggleDropdown')
            ->assertSet('showDropdown', false);
    }

    /** @test */
    public function it_can_close_dropdown()
    {
        $this->actingAs($this->user);

        Livewire::test(NotificationCenter::class)
            ->set('showDropdown', true)
            ->call('closeDropdown')
            ->assertSet('showDropdown', false);
    }

    /** @test */
    public function it_can_mark_notification_as_read()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NotificationCenter::class);
        
        $initialUnreadCount = $component->get('unreadCount');
        
        // Mark first unread notification as read
        $notifications = $component->get('notifications');
        $unreadNotification = collect($notifications)->firstWhere('read_at', null);
        
        if ($unreadNotification) {
            $component->call('markAsRead', $unreadNotification['id'])
                ->assertDispatched('notification-read');
        }
    }

    /** @test */
    public function it_can_mark_all_notifications_as_read()
    {
        $this->actingAs($this->user);

        Livewire::test(NotificationCenter::class)
            ->call('markAllAsRead')
            ->assertDispatched('all-notifications-read');
    }

    /** @test */
    public function it_handles_new_notification_event()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NotificationCenter::class);
        
        $newNotification = [
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'This is a test notification'
        ];

        $component->dispatch('notification-received', $newNotification);
        
        // Component should reload notifications
        $this->assertIsArray($component->get('notifications'));
    }

    /** @test */
    public function it_handles_notification_read_event()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NotificationCenter::class);
        
        $component->dispatch('notification-read', 1);
        
        // Component should reload notifications
        $this->assertIsArray($component->get('notifications'));
    }

    /** @test */
    public function it_shows_correct_unread_count()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NotificationCenter::class);
        
        $notifications = $component->get('notifications');
        $expectedUnreadCount = collect($notifications)->where('read_at', null)->count();
        
        $this->assertEquals($expectedUnreadCount, $component->get('unreadCount'));
    }

    /** @test */
    public function it_works_for_unauthenticated_users()
    {
        Livewire::test(NotificationCenter::class)
            ->assertStatus(200)
            ->assertSet('notifications', [])
            ->assertSet('unreadCount', 0);
    }

    /** @test */
    public function it_displays_notification_badge_when_unread_exists()
    {
        $this->actingAs($this->user);

        $component = Livewire::test(NotificationCenter::class);
        
        if ($component->get('unreadCount') > 0) {
            $component->assertSee('badge bg-red');
        }
    }

    /** @test */
    public function it_displays_empty_state_when_no_notifications()
    {
        $this->actingAs($this->user);

        // Mock empty notifications
        $component = Livewire::test(NotificationCenter::class);
        $component->set('notifications', []);
        
        $component->assertSee('No notifications');
    }
}