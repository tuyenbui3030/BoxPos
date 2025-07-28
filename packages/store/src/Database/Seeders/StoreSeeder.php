<?php

namespace Packages\Store\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedSampleStores();
            $this->createUserStoreRelationships();
        });
    }

    /**
     * Create 2-3 sample stores with complete information
     */
    private function seedSampleStores(): void
    {
        $this->logSeedingProgress('creating_sample_stores');

        // Check if stores already exist to prevent duplicates
        if (Store::where('name', 'Cửa hàng Vật liệu Xây dựng Hòa Bình')->exists()) {
            $this->logSeedingProgress('sample_stores_already_exist');
            $this->stores = Store::whereIn('name', [
                'Cửa hàng Vật liệu Xây dựng Hòa Bình',
                'Đại lý Sắt thép Đại Phát',
                'Cửa hàng Gạch ốp lát Minh Phú'
            ])->get();
            return;
        }

        // Create main construction materials store
        $mainStore = Store::create([
            'name' => 'Cửa hàng Vật liệu Xây dựng Hòa Bình',
            'slug' => Store::generateSlug('Cửa hàng Vật liệu Xây dựng Hòa Bình'),
            'description' => 'Chuyên cung cấp vật liệu xây dựng chất lượng cao với giá cả cạnh tranh. Phục vụ các công trình từ nhỏ đến lớn.',
            'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            'phone' => '028.3822.1234',
            'email' => 'info@hoabinhvatlieu.com',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'language' => 'vi',
            'settings' => $this->getStoreSettings(),
            'status' => Store::STATUS_ACTIVE,
        ]);

        // Create secondary store
        $secondaryStore = Store::create([
            'name' => 'Đại lý Sắt thép Đại Phát',
            'slug' => Store::generateSlug('Đại lý Sắt thép Đại Phát'),
            'description' => 'Chuyên phân phối sắt thép các loại, thép hình, thép tấm cho các công trình xây dựng.',
            'address' => '456 Lê Lợi, Quận 3, TP.HCM',
            'phone' => '028.3933.5678',
            'email' => 'contact@daiphatthep.com',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'language' => 'vi',
            'settings' => $this->getStoreSettings(),
            'status' => Store::STATUS_ACTIVE,
        ]);

        // Create third store for development environment
        $thirdStore = null;
        if ($this->isDevelopment) {
            $thirdStore = Store::create([
                'name' => 'Cửa hàng Gạch ốp lát Minh Phú',
                'slug' => Store::generateSlug('Cửa hàng Gạch ốp lát Minh Phú'),
                'description' => 'Chuyên cung cấp gạch ốp lát, gạch men, đá granite và các sản phẩm hoàn thiện nội thất.',
                'address' => '789 Trần Hưng Đạo, Quận 5, TP.HCM',
                'phone' => '028.3855.9012',
                'email' => 'sales@minhphugach.com',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'language' => 'vi',
                'settings' => $this->getStoreSettings(),
                'status' => Store::STATUS_ACTIVE,
            ]);
        }

        $this->logSeedingProgress('sample_stores_created', [
            'main_store_id' => $mainStore->id,
            'secondary_store_id' => $secondaryStore->id,
            'third_store_id' => $thirdStore?->id,
            'total_stores' => Store::count()
        ]);

        // Store the created stores for later use
        $this->stores = collect([$mainStore, $secondaryStore, $thirdStore])->filter();
    }

    /**
     * Create user-store relationships for admin user
     */
    private function createUserStoreRelationships(): void
    {
        $this->logSeedingProgress('creating_user_store_relationships');

        // Get admin user
        $adminUser = User::where('email', 'admin@boxpos.com')->first();
        
        if (!$adminUser) {
            $this->logError(new \Exception('Admin user not found'), [
                'operation' => 'create_user_store_relationships'
            ]);
            throw new \Exception('Admin user not found. Please run UserSeeder first.');
        }

        $stores = $this->getStores();
        $firstStore = $stores->first();

        foreach ($stores as $index => $store) {
            // Check if relationship already exists
            if (!$adminUser->stores()->where('store_id', $store->id)->exists()) {
                // Create user-store relationship
                $adminUser->stores()->attach($store->id, [
                    'role' => 'admin',
                    'permissions' => json_encode($this->getAdminPermissions()),
                    'is_active' => true,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->logSeedingProgress('user_store_relationship_created', [
                    'user_id' => $adminUser->id,
                    'store_id' => $store->id,
                    'role' => 'admin'
                ]);
            } else {
                $this->logSeedingProgress('user_store_relationship_already_exists', [
                    'user_id' => $adminUser->id,
                    'store_id' => $store->id
                ]);
            }
        }

        // Set current store for admin user (first store) if not already set
        if (!$adminUser->current_store_id) {
            $adminUser->update(['current_store_id' => $firstStore->id]);
        }

        $this->logSeedingProgress('admin_current_store_set', [
            'user_id' => $adminUser->id,
            'current_store_id' => $firstStore->id,
            'store_name' => $firstStore->name
        ]);

        // Create relationships for demo users in development
        if ($this->isDevelopment) {
            $this->createDemoUserStoreRelationships();
        }

        $this->logSeedingProgress('user_store_relationships_completed', [
            'admin_stores_count' => $adminUser->stores()->count(),
            'total_relationships' => DB::table('user_stores')->count()
        ]);
    }

    /**
     * Create store relationships for demo users
     */
    private function createDemoUserStoreRelationships(): void
    {
        $demoUsers = User::whereIn('email', [
            'manager@boxpos.com',
            'staff@boxpos.com',
            'accountant@boxpos.com'
        ])->get();

        $stores = $this->getStores();

        foreach ($demoUsers as $user) {
            // Assign each demo user to 1-2 random stores
            $userStores = $stores->random(fake()->numberBetween(1, 2));
            
            foreach ($userStores as $store) {
                // Check if relationship already exists
                if (!$user->stores()->where('store_id', $store->id)->exists()) {
                    $role = $this->getDemoUserRole($user->email);
                    
                    $user->stores()->attach($store->id, [
                        'role' => $role,
                        'permissions' => json_encode($this->getRolePermissions($role)),
                        'is_active' => true,
                        'joined_at' => fake()->dateTimeBetween('-30 days', 'now'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->logSeedingProgress('demo_user_store_relationship_created', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'store_id' => $store->id,
                        'role' => $role
                    ]);
                }
            }

            // Set current store for demo user if not already set
            if (!$user->current_store_id && $userStores->isNotEmpty()) {
                $user->update(['current_store_id' => $userStores->first()->id]);
            }
        }
    }

    /**
     * Get store settings configuration
     */
    private function getStoreSettings(): array
    {
        return [
            'business_hours' => [
                'monday' => ['open' => '08:00', 'close' => '18:00'],
                'tuesday' => ['open' => '08:00', 'close' => '18:00'],
                'wednesday' => ['open' => '08:00', 'close' => '18:00'],
                'thursday' => ['open' => '08:00', 'close' => '18:00'],
                'friday' => ['open' => '08:00', 'close' => '18:00'],
                'saturday' => ['open' => '08:00', 'close' => '17:00'],
                'sunday' => ['open' => '09:00', 'close' => '16:00'],
            ],
            'tax_settings' => [
                'vat_rate' => 10,
                'tax_number' => fake()->numerify('##########'),
                'company_registration' => fake()->numerify('############'),
            ],
            'inventory_settings' => [
                'low_stock_threshold' => 10,
                'auto_reorder' => false,
                'track_serial_numbers' => false,
                'enable_barcode' => true,
            ],
            'notification_settings' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'low_stock_alerts' => true,
                'order_notifications' => true,
            ],
            'display_settings' => [
                'theme' => 'light',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'decimal_places' => 0,
                'currency_symbol' => '₫',
            ],
            'payment_settings' => [
                'accept_cash' => true,
                'accept_bank_transfer' => true,
                'accept_credit_card' => false,
                'payment_terms_days' => 30,
            ],
        ];
    }

    /**
     * Get admin permissions
     */
    private function getAdminPermissions(): array
    {
        return [
            'users.manage',
            'stores.manage',
            'inventory.manage',
            'sales.manage',
            'reports.view',
            'settings.manage',
            'employees.manage',
            'customers.manage',
            'suppliers.manage',
            'financial.manage',
        ];
    }

    /**
     * Get role-specific permissions
     */
    private function getRolePermissions(string $role): array
    {
        return match ($role) {
            'manager' => [
                'inventory.manage',
                'sales.manage',
                'reports.view',
                'employees.view',
                'customers.manage',
                'suppliers.view',
            ],
            'staff' => [
                'inventory.view',
                'sales.create',
                'customers.view',
                'suppliers.view',
            ],
            'viewer' => [
                'inventory.view',
                'sales.view',
                'reports.view',
            ],
            default => [],
        };
    }

    /**
     * Get demo user role based on email
     */
    private function getDemoUserRole(string $email): string
    {
        return match ($email) {
            'manager@boxpos.com' => 'manager',
            'staff@boxpos.com' => 'staff',
            'accountant@boxpos.com' => 'viewer',
            default => 'staff',
        };
    }
}
