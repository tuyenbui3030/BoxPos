<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\UserSubscription;
use Packages\User\Models\User;
use Packages\Store\Models\Store;

class UserSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $buildingPosApp = Application::where('slug', 'building-pos')->first();
        $clinicPosApp = Application::where('slug', 'clinic-pos')->first();
        
        if (!$buildingPosApp || !$clinicPosApp) {
            $this->command->error('❌ Applications not found. Run ApplicationSeeder first.');
            return;
        }

        // Tạo subscription cho Super Admin
        $superAdmin = User::where('email', 'superadmin@boxpos.vn')->first();
        if ($superAdmin) {
            // BuildingPos Enterprise subscription
            $buildingSubscription = UserSubscription::create([
                'user_id' => $superAdmin->id,
                'application_id' => $buildingPosApp->id,
                'plan_type' => 'enterprise',
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'max_stores' => -1, // Unlimited
                'features' => ['*'], // All features
                'monthly_price' => 1299000,
                'billing_info' => [
                    'payment_method' => 'demo',
                    'billing_cycle' => 'monthly',
                ],
                'next_billing_at' => now()->addMonth(),
            ]);

            // ClinicPos Professional subscription (Beta access)
            $clinicSubscription = UserSubscription::create([
                'user_id' => $superAdmin->id,
                'application_id' => $clinicPosApp->id,
                'plan_type' => 'professional',
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3), // 3 months beta
                'max_stores' => 2,
                'features' => ['*'], // All features
                'monthly_price' => 0, // Free beta
                'billing_info' => [
                    'payment_method' => 'beta',
                    'billing_cycle' => 'monthly',
                ],
                'next_billing_at' => now()->addMonths(3),
            ]);

            // Update existing stores to link with BuildingPos
            $stores = Store::all();
            foreach ($stores as $store) {
                $store->update([
                    'application_id' => $buildingPosApp->id,
                    'subscription_id' => $buildingSubscription->id,
                    'app_settings' => [
                        'inventory_tracking' => true,
                        'auto_reorder' => false,
                        'price_alerts' => true,
                        'supplier_integration' => true,
                    ],
                ]);
            }

            $this->command->info("✅ Created subscriptions for Super Admin:");
            $this->command->info("   🏗️  BuildingPos Enterprise - Unlimited stores");
            $this->command->info("   🏥 ClinicPos Professional (Beta) - 2 stores");
        }

        // Tạo subscription cho Multi Manager
        $multiManager = User::where('email', 'multimanager@boxpos.vn')->first();
        if ($multiManager) {
            $subscription = UserSubscription::create([
                'user_id' => $multiManager->id,
                'application_id' => $buildingPosApp->id,
                'plan_type' => 'professional',
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'max_stores' => 3,
                'features' => ['*'],
                'monthly_price' => 599000,
                'billing_info' => [
                    'payment_method' => 'demo',
                    'billing_cycle' => 'monthly',
                ],
                'next_billing_at' => now()->addMonth(),
            ]);

            $this->command->info("✅ Created BuildingPos Professional subscription for Multi Manager");
        }

        // Tạo trial subscriptions cho một số users khác
        $trialUsers = User::whereNotIn('email', ['superadmin@boxpos.vn', 'multimanager@boxpos.vn'])
                         ->limit(3)
                         ->get();

        foreach ($trialUsers as $user) {
            UserSubscription::create([
                'user_id' => $user->id,
                'application_id' => $buildingPosApp->id,
                'plan_type' => 'trial',
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays(14),
                'max_stores' => 1,
                'features' => ['basic_inventory', 'basic_sales', 'basic_reports'],
                'monthly_price' => 0,
                'billing_info' => [
                    'payment_method' => 'trial',
                    'billing_cycle' => 'monthly',
                ],
                'next_billing_at' => now()->addDays(14),
            ]);
        }

        if ($trialUsers->count() > 0) {
            $this->command->info("✅ Created {$trialUsers->count()} trial subscriptions");
        }

        $this->command->info('');
        $this->command->info('📊 Subscription Summary:');
        $this->command->info('   🏗️  BuildingPos: ' . UserSubscription::whereHas('application', fn($q) => $q->where('slug', 'building-pos'))->count() . ' subscriptions');
        $this->command->info('   🏥 ClinicPos: ' . UserSubscription::whereHas('application', fn($q) => $q->where('slug', 'clinic-pos'))->count() . ' subscriptions');
        $this->command->info('   📈 Active: ' . UserSubscription::where('status', 'active')->count());
        $this->command->info('   🆓 Trial: ' . UserSubscription::where('plan_type', 'trial')->count());
    }
}