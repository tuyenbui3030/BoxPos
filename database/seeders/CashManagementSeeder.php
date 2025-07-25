<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\CashManagement\Models\CashCategory;
use Packages\CashManagement\Models\CashAccount;
use Packages\CashManagement\Models\CashTransaction;
use Packages\User\Models\User;
use Carbon\Carbon;

class CashManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💰 Seeding Cash Management...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createCashManagementForStore($store);
        }

        $this->command->info('✅ Cash Management seeded successfully!');
    }

    private function createCashManagementForStore(Store $store): void
    {
        $createdBy = User::where('email', 'admin@' . strtolower($store->slug) . '.boxpos.vn')->first();

        // Create cash categories
        $categories = $this->createCashCategories($store, $createdBy);
        
        // Create cash accounts
        $accounts = $this->createCashAccounts($store, $createdBy);
        
        // Create cash transactions
        $this->createCashTransactions($store, $categories, $accounts, $createdBy);
    }

    private function createCashCategories(Store $store, ?User $createdBy): array
    {
        $categories = [
            ['name' => 'Doanh thu bán hàng', 'type' => 'income', 'code' => 'SALES'],
            ['name' => 'Thu khác', 'type' => 'income', 'code' => 'OTHER_INCOME'],
            ['name' => 'Chi phí vận hành', 'type' => 'expense', 'code' => 'OPERATING'],
            ['name' => 'Chi phí nhân viên', 'type' => 'expense', 'code' => 'STAFF'],
            ['name' => 'Chi phí marketing', 'type' => 'expense', 'code' => 'MARKETING'],
            ['name' => 'Chi phí khác', 'type' => 'expense', 'code' => 'OTHER_EXPENSE'],
        ];

        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $category = CashCategory::create([
                ...$categoryData,
                'store_id' => $store->id,
                'is_active' => true,
            ]);
            $createdCategories[] = $category;
        }

        return $createdCategories;
    }

    private function createCashAccounts(Store $store, ?User $createdBy): array
    {
        $accounts = [
            [
                'account_code' => 'CASH001',
                'account_name' => 'Tiền mặt quầy thu ngân',
                'account_type' => 'cash',
                'account_number' => 'CASH-001',
                'opening_balance' => 5000000,
                'opening_date' => '2025-01-01',
            ],
            [
                'account_code' => 'VCB001',
                'account_name' => 'Tài khoản ngân hàng VCB',
                'account_type' => 'bank',
                'account_number' => 'VCB-1234567890',
                'opening_balance' => 50000000,
                'opening_date' => '2025-01-01',
            ],
            [
                'account_code' => 'TCB001',
                'account_name' => 'Tài khoản ngân hàng TCB',
                'account_type' => 'bank',
                'account_number' => 'TCB-0987654321',
                'opening_balance' => 30000000,
                'opening_date' => '2025-01-01',
            ],
        ];

        $createdAccounts = [];
        foreach ($accounts as $accountData) {
            $account = CashAccount::create([
                ...$accountData,
                'store_id' => $store->id,
                'currency' => 'VND',
                'current_balance' => $accountData['opening_balance'],
                'is_active' => true,
            ]);
            $createdAccounts[] = $account;
        }

        return $createdAccounts;
    }

    private function createCashTransactions(Store $store, array $categories, array $accounts, ?User $createdBy): void
    {
        // Create 30-50 transactions for the last 30 days
        $transactionCount = rand(30, 50);
        
        for ($i = 0; $i < $transactionCount; $i++) {
            $this->createCashTransaction($store, $categories, $accounts, $createdBy);
        }
    }

    private function createCashTransaction(Store $store, array $categories, array $accounts, ?User $createdBy): void
    {
        $transactionDate = Carbon::now()->subDays(rand(0, 30));
        $category = $categories[array_rand($categories)];
        $account = $accounts[array_rand($accounts)];
        
        $transactionType = $category->type === 'income' ? 'income' : 'expense';
        $amount = $this->getRandomAmount($category->type);

        CashTransaction::create([
            'store_id' => $store->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'transaction_number' => $this->generateTransactionNumber($transactionDate),
            'transaction_date' => $transactionDate,
            'type' => $transactionType,
            'amount' => $amount,
            'currency' => 'VND',
            'base_amount' => $amount,
            'description' => $this->getTransactionDescription($category),
            'payment_reference' => $this->generateReferenceNumber(),
            'payment_method' => $this->getPaymentMethod($account->account_type),
            'status' => 'completed',
            'notes' => $this->getTransactionNotes($category),
            'created_by' => $createdBy?->id,
            'is_approved' => true,
            'approved_by' => $createdBy?->id,
            'approved_at' => $transactionDate->copy()->addHours(rand(1, 4)),
        ]);
    }

    private function generateTransactionNumber(Carbon $date): string
    {
        return 'TXN-' . $date->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function getRandomAmount(string $categoryType): float
    {
        if ($categoryType === 'income') {
            return rand(100000, 5000000); // 100k - 5M VND
        } else {
            return rand(50000, 2000000); // 50k - 2M VND
        }
    }

    private function getTransactionDescription(CashCategory $category): string
    {
        $descriptions = [
            'SALES' => 'Thu tiền bán hàng',
            'OTHER_INCOME' => 'Thu nhập khác',
            'OPERATING' => 'Chi phí vận hành',
            'STAFF' => 'Chi lương nhân viên',
            'MARKETING' => 'Chi phí quảng cáo',
            'OTHER_EXPENSE' => 'Chi phí khác',
        ];
        
        return $descriptions[$category->code] ?? 'Giao dịch tiền mặt';
    }

    private function generateReferenceNumber(): string
    {
        return 'REF-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function getPaymentMethod(string $accountType): string
    {
        return $accountType === 'cash' ? 'cash' : 'bank_transfer';
    }

    private function getTransactionNotes(CashCategory $category): ?string
    {
        $notes = [
            'SALES' => 'Thu từ bán hàng trong ngày',
            'STAFF' => 'Thanh toán lương tháng',
            'OPERATING' => 'Chi phí điện nước, thuê mặt bằng',
            'MARKETING' => 'Chi phí quảng cáo Facebook',
        ];
        
        return $notes[$category->code] ?? null;
    }
}
