<?php

namespace Packages\User\Tests\Feature;

use Packages\User\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * User Authentication Test
 * 
 * Feature tests for User authentication endpoints.
 */
class UserAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup test data if needed
    }

    public function test_user_can_register(): void
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!'
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'created_at'
                     ]
                 ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        // Act
        $response = $this->postJson('/api/auth/login', $credentials);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'user' => ['id', 'name', 'email'],
                         'token'
                     ]
                 ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('correct_password')
        ]);

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrong_password'
        ];

        // Act
        $response = $this->postJson('/api/auth/login', $credentials);

        // Assert
        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Invalid credentials'
                 ]);
    }

    public function test_user_can_change_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('old_password')
        ]);

        $this->actingAs($user);

        $passwordData = [
            'current_password' => 'old_password',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!'
        ];

        // Act
        $response = $this->putJson('/api/auth/change-password', $passwordData);

        // Assert
        $response->assertStatus(200);
        
        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct_current_password')
        ]);

        $this->actingAs($user);

        $passwordData = [
            'current_password' => 'wrong_current_password',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!'
        ];

        // Act
        $response = $this->putJson('/api/auth/change-password', $passwordData);

        // Assert
        $response->assertStatus(401);
    }

    public function test_registration_validates_required_fields(): void
    {
        // Act
        $response = $this->postJson('/api/auth/register', []);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_validates_email_uniqueness(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!'
        ];

        // Act
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_logout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->postJson('/api/auth/logout');

        // Assert
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $this->actingAs($user);

        // Act
        $response = $this->getJson('/api/auth/profile');

        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $user->id,
                         'name' => 'John Doe',
                         'email' => 'john@example.com'
                     ]
                 ]);
    }
}
