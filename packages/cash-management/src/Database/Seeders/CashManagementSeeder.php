<?php

namespace Packages\CashManagement\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Illuminate\Support\Facades\DB;
use Packages\CashManagement\Models\CashAccount;
use Packages\CashManagement\Models\CashCategory;
use Packages\CashManagement\Models\CashTransaction;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

class CashManagementSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedCashCategories();
            $this->seedCashAccounts();
            $this->seedCashTransactions();
        });
    }

    /**
     * Seed cash categories
     */
    private function seedCashCategories(): void
    {
        $this->logSeedingProgress('seeding_cash_categories_started');

        // Standard cash categories that apply to all stores
        $standardCategories = [
            [
                'code' => 'INCOME_SALES',
                'name' => 'Thu tiền bán hàng',
                'description' => 'Thu tiền từ việc bán hàng hóa, vật liệu xây dựng',
                'type' => 'income',
                'color' => '#28a745',
                'is_system' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'INCOME_DEBT',
                'name' => 'Thu nợ khách hàng',
                'description' => 'Thu tiền nợ từ khách hàng',
                'type' => 'income',
                'color' => '#17a2b8',
                'is_system' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'INCOME_OTHER',
                'name' => 'Thu khác',
                'description' => 'Các khoản thu khác',
                'type' => 'income',
                'color' => '#6f42c1',
                'is_system' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'EXPENSE_PURCHASE',
                'name' => 'Chi mua hàng',
                'description' => 'Chi phí mua hàng hóa, vật liệu từ nhà cung cấp',
                'type' => 'expense',
                'color' => '#dc3545',
                'is_system' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'EXPENSE_SUPPLIER',
                'name' => 'Trả nợ nhà cung cấp',
                'description' => 'Thanh toán nợ cho nhà cung cấp',
                'type' => 'expense',
                'color' => '#fd7e14',
                'is_system' => true,
                'sort_order' => 5,
            ],
            [
                'code' => 'EXPENSE_OPERATION',
                'name' => 'Chi phí vận hành',
                'description' => 'Chi phí điện, nước, thuê mặt bằng, lương nhân viên',
                'type' => 'expense',
                'color' => '#ffc107',
                'is_system' => true,
                'sort_order' => 6,
            ],
            [
                'code' => 'EXPENSE_OTHER',
                'name' => 'Chi khác',
                'description' => 'Các khoản chi khác',
                'type' => 'expense',
                'color' => '#6c757d',
                'is_system' => true,
                'sort_order' => 7,
            ],
            [
                'code' => 'TRANSFER',
                'name' => 'Chuyển khoản nội bộ',
                'description' => 'Chuyển tiền giữa các tài khoản trong cửa hàng',
                'type' => 'transfer',
                'color' => '#20c997',
                'is_system' => true,
                'sort_order' => 8,
            ],
        ];

        $stores = $this->getStores();
        
        foreach ($stores as $store) {
            $this->logSeedingProgress('seeding_cash_categories_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);

            foreach ($standardCategories as $categoryData) {
                CashCategory::create(array_merge($categoryData, [
                    'store_id' => $store->id,
                    'is_active' => true,
                ]));
            }

            // Add some custom categories for development environment
            if ($this->isDevelopment) {
                $customCategories = [
                    [
                        'code' => 'INCOME_BONUS',
                        'name' => 'Thu thưởng',
                        'description' => 'Tiền thưởng từ nhà cung cấp',
                        'type' => 'income',
                        'color' => '#e83e8c',
                        'is_system' => false,
                        'sort_order' => 9,
                    ],
                    [
                        'code' => 'EXPENSE_MARKETING',
                        'name' => 'Chi marketing',
                        'description' => 'Chi phí quảng cáo, khuyến mãi',
                        'type' => 'expense',
                        'color' => '#fd7e14',
                        'is_system' => false,
                        'sort_order' => 10,
                    ],
                ];

                foreach ($customCategories as $categoryData) {
                    CashCategory::create(array_merge($categoryData, [
                        'store_id' => $store->id,
                        'is_active' => true,
                    ]));
                }
            }
        }

        $this->logSeedingProgress('seeding_cash_categories_completed');
    }

    /**
     * Seed cash accounts for each store
     */
    private function seedCashAccounts(): void
    {
        $this->logSeedingProgress('seeding_cash_accounts_started');

        $stores = $this->getStores();
        
        foreach ($stores as $store) {
            $this->logSeedingProgress('seeding_cash_accounts_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);

            $adminUser = User::where('email', 'admin@boxpos.com')->first();
            
            // Standard accounts for each store
            $accounts = [
                [
                    'account_code' => CashAccount::generateAccountCode($store->id, 'cash'),
                    'account_name' => 'Tiền mặt tại quầy',
                    'description' => 'Tài khoản tiền mặt chính tại quầy thu ngân',
                    'account_type' => 'cash',
                    'currency' => 'VND',
                    'opening_balance' => $this->isDevelopment ? 5000000 : 1000000, // 5M or 1M VND
                    'current_balance' => $this->isDevelopment ? 5000000 : 1000000,
                    'opening_date' => now()->subDays(30),
                    'is_active' => true,
                    'is_default' => true,
                    'allow_negative' => false,
                ],
                [
                    'account_code' => CashAccount::generateAccountCode($store->id, 'bank'),
                    'account_name' => 'Tài khoản ngân hàng Vietcombank',
                    'description' => 'Tài khoản ngân hàng chính của cửa hàng',
                    'account_type' => 'bank',
                    'currency' => 'VND',
                    'bank_name' => 'Vietcombank',
                    'account_number' => '0123456789' . str_pad($store->id, 3, '0', STR_PAD_LEFT),
                    'account_holder' => $store->name,
                    'branch' => 'Chi nhánh Quận 1',
                    'opening_balance' => $this->isDevelopment ? 50000000 : 10000000, // 50M or 10M VND
                    'current_balance' => $this->isDevelopment ? 50000000 : 10000000,
                    'opening_date' => now()->subDays(30),
                    'is_active' => true,
                    'is_default' => false,
                    'allow_negative' => false,
                ],
                [
                    'account_code' => CashAccount::generateAccountCode($store->id, 'petty_cash'),
                    'account_name' => 'Quỹ tiền mặt nhỏ',
                    'description' => 'Quỹ tiền mặt cho các chi phí nhỏ hàng ngày',
                    'account_type' => 'petty_cash',
                    'currency' => 'VND',
                    'opening_balance' => $this->isDevelopment ? 2000000 : 500000, // 2M or 500K VND
                    'current_balance' => $this->isDevelopment ? 2000000 : 500000,
                    'opening_date' => now()->subDays(30),
                    'daily_limit' => 500000, // 500K VND daily limit
                    'is_active' => true,
                    'is_default' => false,
                    'allow_negative' => false,
                ],
            ];

            // Add more accounts for development environment
            if ($this->isDevelopment) {
                $developmentAccounts = [
                    [
                        'account_code' => CashAccount::generateAccountCode($store->id, 'bank'),
                        'account_name' => 'Tài khoản ngân hàng BIDV',
                        'description' => 'Tài khoản ngân hàng phụ',
                        'account_type' => 'bank',
                        'currency' => 'VND',
                        'bank_name' => 'BIDV',
                        'account_number' => '9876543210' . str_pad($store->id, 3, '0', STR_PAD_LEFT),
                        'account_holder' => $store->name,
                        'branch' => 'Chi nhánh Quận 3',
                        'opening_balance' => 20000000, // 20M VND
                        'current_balance' => 20000000,
                        'opening_date' => now()->subDays(20),
                        'is_active' => true,
                        'is_default' => false,
                        'allow_negative' => false,
                    ],
                    [
                        'account_code' => CashAccount::generateAccountCode($store->id, 'e_wallet'),
                        'account_name' => 'Ví điện tử MoMo',
                        'description' => 'Tài khoản ví điện tử MoMo',
                        'account_type' => 'e_wallet',
                        'currency' => 'VND',
                        'account_number' => '0987654321',
                        'opening_balance' => 5000000, // 5M VND
                        'current_balance' => 5000000,
                        'opening_date' => now()->subDays(15),
                        'daily_limit' => 10000000, // 10M VND daily limit
                        'is_active' => true,
                        'is_default' => false,
                        'allow_negative' => false,
                    ],
                ];

                $accounts = array_merge($accounts, $developmentAccounts);
            }

            foreach ($accounts as $accountData) {
                CashAccount::create(array_merge($accountData, [
                    'store_id' => $store->id,
                    'created_by' => $adminUser?->id,
                ]));
            }
        }

        $this->logSeedingProgress('seeding_cash_accounts_completed');
    }

    /**
     * Seed cash transactions with balance consistency
     */
    private function seedCashTransactions(): void
    {
        $this->logSeedingProgress('seeding_cash_transactions_started');

        $stores = $this->getStores();
        
        foreach ($stores as $store) {
            $this->logSeedingProgress('seeding_cash_transactions_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);

            $accounts = CashAccount::where('store_id', $store->id)->get();
            $categories = CashCategory::where('store_id', $store->id)->get();
            $adminUser = User::where('email', 'admin@boxpos.com')->first();

            $transactionsPerAccount = $this->getConfigValue('cash_transactions_per_account', 10);

            foreach ($accounts as $account) {
                $this->seedTransactionsForAccount($account, $categories, $adminUser, $transactionsPerAccount);
            }
        }

        $this->logSeedingProgress('seeding_cash_transactions_completed');
    }

    /**
     * Seed transactions for a specific account
     */
    private function seedTransactionsForAccount(CashAccount $account, $categories, ?User $user, int $count): void
    {
        $runningBalance = $account->opening_balance;
        $startDate = $account->opening_date;
        $endDate = now();

        for ($i = 0; $i < $count; $i++) {
            // Generate random transaction date between opening date and now
            $transactionDate = Carbon::parse($startDate)->addDays(rand(0, $startDate->diffInDays($endDate)));
            
            // Randomly choose transaction type based on account balance
            $transactionTypes = ['income', 'expense'];
            
            // Avoid negative balance for accounts that don't allow it
            if (!$account->allow_negative && $runningBalance < 100000) {
                $transactionTypes = ['income']; // Only income to avoid negative balance
            }
            
            $transactionType = $transactionTypes[array_rand($transactionTypes)];
            
            // Get appropriate category for transaction type
            $availableCategories = $categories->where('type', $transactionType);
            if ($availableCategories->isEmpty()) {
                continue;
            }
            
            $category = $availableCategories->random();
            
            // Generate realistic transaction amounts
            $amount = $this->generateTransactionAmount($transactionType, $account->account_type);
            
            // Adjust running balance
            if ($transactionType === 'income') {
                $runningBalance += $amount;
            } else {
                $runningBalance -= $amount;
                
                // Ensure we don't go negative if not allowed
                if (!$account->allow_negative && $runningBalance < 0) {
                    $amount = $runningBalance + $amount; // Reduce amount to avoid negative
                    $runningBalance = 0;
                }
            }

            $transaction = CashTransaction::create([
                'store_id' => $account->store_id,
                'account_id' => $account->id,
                'category_id' => $category->id,
                'transaction_number' => CashTransaction::generateTransactionNumber($account->store_id),
                'transaction_date' => $transactionDate,
                'type' => $transactionType,
                'amount' => $amount,
                'currency' => $account->currency,
                'exchange_rate' => 1.0,
                'base_amount' => $amount,
                'payment_method' => $this->getPaymentMethodForAccount($account),
                'payer_payee' => $this->generatePayerPayee($transactionType),
                'description' => $this->generateTransactionDescription($transactionType, $category->name),
                'status' => 'completed',
                'is_reconciled' => rand(0, 100) < 80, // 80% reconciled
                'reconciled_date' => rand(0, 100) < 80 ? $transactionDate->addDays(rand(1, 5)) : null,
                'reconciled_by' => rand(0, 100) < 80 ? $user?->id : null,
                'requires_approval' => $amount >= 1000000, // Require approval for amounts >= 1M VND
                'is_approved' => true,
                'approved_by' => $amount >= 1000000 ? $user?->id : null,
                'approved_at' => $amount >= 1000000 ? $transactionDate->addMinutes(rand(5, 60)) : null,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ]);
        }

        // Update account balance to match the running balance
        $account->update(['current_balance' => $runningBalance]);
    }

    /**
     * Generate realistic transaction amounts based on type and account
     */
    private function generateTransactionAmount(string $type, string $accountType): float
    {
        if ($type === 'income') {
            return match($accountType) {
                'cash' => rand(50000, 2000000), // 50K - 2M VND for cash sales
                'bank' => rand(1000000, 50000000), // 1M - 50M VND for bank transfers
                'petty_cash' => rand(10000, 200000), // 10K - 200K VND for petty cash
                'e_wallet' => rand(100000, 5000000), // 100K - 5M VND for e-wallet
                default => rand(100000, 1000000),
            };
        } else {
            return match($accountType) {
                'cash' => rand(20000, 1000000), // 20K - 1M VND for cash expenses
                'bank' => rand(500000, 20000000), // 500K - 20M VND for bank payments
                'petty_cash' => rand(5000, 100000), // 5K - 100K VND for petty expenses
                'e_wallet' => rand(50000, 2000000), // 50K - 2M VND for e-wallet payments
                default => rand(50000, 500000),
            };
        }
    }

    /**
     * Get appropriate payment method for account type
     */
    private function getPaymentMethodForAccount(CashAccount $account): string
    {
        return match($account->account_type) {
            'cash', 'petty_cash' => 'cash',
            'bank' => 'bank_transfer',
            'e_wallet' => 'e_wallet',
            'credit_card' => 'credit_card',
            default => 'cash',
        };
    }

    /**
     * Generate realistic payer/payee names
     */
    private function generatePayerPayee(string $type): string
    {
        if ($type === 'income') {
            $customers = [
                'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung',
                'Hoàng Văn Em', 'Vũ Thị Phương', 'Đặng Văn Giang', 'Bùi Thị Hoa',
                'Công ty TNHH Xây dựng ABC', 'Công ty CP Đầu tư XYZ'
            ];
            return $customers[array_rand($customers)];
        } else {
            $suppliers = [
                'Công ty TNHH Xi măng Hòa Bình', 'Công ty CP Sắt thép Việt Nam',
                'Công ty TNHH Gạch Đồng Tâm', 'Công ty CP Cát đá Minh Phú',
                'Điện lực TP.HCM', 'Công ty Cấp nước Sài Gòn', 'Nhân viên bán hàng'
            ];
            return $suppliers[array_rand($suppliers)];
        }
    }

    /**
     * Generate transaction descriptions
     */
    private function generateTransactionDescription(string $type, string $categoryName): string
    {
        if ($type === 'income') {
            $descriptions = [
                'Thu tiền bán xi măng PCB40',
                'Thu tiền bán sắt thép Hòa Phát',
                'Thu tiền bán gạch đỏ',
                'Thu nợ khách hàng tháng trước',
                'Thu tiền bán cát vàng',
                'Thu tiền dịch vụ vận chuyển'
            ];
        } else {
            $descriptions = [
                'Mua xi măng từ nhà cung cấp',
                'Thanh toán tiền điện tháng ' . now()->format('m/Y'),
                'Thanh toán tiền nước tháng ' . now()->format('m/Y'),
                'Chi lương nhân viên',
                'Mua sắt thép từ công ty Hòa Phát',
                'Chi phí xăng xe vận chuyển',
                'Mua văn phòng phẩm'
            ];
        }

        return $descriptions[array_rand($descriptions)];
    }
}