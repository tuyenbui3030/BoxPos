<?php

namespace Packages\User\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\User\Models\User;
use Packages\User\Models\UserDevice;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedAdminUser();
            $this->seedDemoUsers();
        });
    }

    /**
     * Create admin user with standard credentials
     */
    private function seedAdminUser(): void
    {
        $this->logSeedingProgress('creating_admin_user');

        $adminUser = User::create([
            'name' => 'Administrator',
            'email' => 'admin@boxpos.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        // Create user devices for admin user
        $this->createUserDevices($adminUser, 2);

        $this->logSeedingProgress('admin_user_created', [
            'user_id' => $adminUser->id,
            'email' => $adminUser->email,
            'devices_count' => $adminUser->devices()->count()
        ]);
    }

    /**
     * Create demo users for development environment
     */
    private function seedDemoUsers(): void
    {
        if (!$this->isDevelopment) {
            return;
        }

        $this->logSeedingProgress('creating_demo_users');

        // Create specific demo users with Vietnamese names
        $demoUsers = [
            [
                'name' => 'Nguyễn Văn Quản lý',
                'email' => 'manager@boxpos.com',
                'password' => Hash::make('manager123'),
            ],
            [
                'name' => 'Trần Thị Nhân viên',
                'email' => 'staff@boxpos.com',
                'password' => Hash::make('staff123'),
            ],
            [
                'name' => 'Lê Văn Kế toán',
                'email' => 'accountant@boxpos.com',
                'password' => Hash::make('accountant123'),
            ],
        ];

        foreach ($demoUsers as $userData) {
            $user = User::create(array_merge($userData, [
                'email_verified_at' => now(),
            ]));

            // Create 1-3 devices for each demo user
            $this->createUserDevices($user, fake()->numberBetween(1, 3));

            $this->logSeedingProgress('demo_user_created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ]);
        }

        // Create additional random users using factory
        $additionalUsersCount = $this->getRecordCount(10, 3);
        
        User::factory($additionalUsersCount)
            ->create()
            ->each(function (User $user) {
                // Create 1-2 devices for each factory user
                $this->createUserDevices($user, fake()->numberBetween(1, 2));
            });

        $this->logSeedingProgress('demo_users_completed', [
            'total_demo_users' => count($demoUsers),
            'additional_users' => $additionalUsersCount,
            'total_users' => User::count()
        ]);
    }

    /**
     * Create user devices for a given user
     */
    private function createUserDevices(User $user, int $count): void
    {
        $devices = [];

        for ($i = 0; $i < $count; $i++) {
            $device = UserDevice::factory()
                ->for($user)
                ->create();

            // Make first device trusted and active for admin
            if ($user->email === 'admin@boxpos.com' && $i === 0) {
                $device->update([
                    'is_trusted' => true,
                    'last_activity' => now(),
                    'last_login_at' => now()->subMinutes(30),
                ]);
            }

            $devices[] = $device->id;
        }

        $this->logSeedingProgress('user_devices_created', [
            'user_id' => $user->id,
            'devices_count' => $count,
            'device_ids' => $devices
        ]);
    }
}
