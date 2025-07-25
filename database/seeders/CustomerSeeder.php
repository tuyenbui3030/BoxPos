<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();
        
        $customerData = [
            [
                'customer_name' => 'Nguyễn Văn An',
                'email' => 'nguyenvanan@gmail.com',
                'phone_number' => '0901234567',
                'address' => '123 Lê Lợi, Quận 1, TP.HCM',
                'birthday' => '1985-05-15',
                'gender' => 'male',
                'customer_group' => 'vip',
                'customer_type' => 'individual',
            ],
            [
                'customer_name' => 'Trần Thị Bình',
                'email' => 'tranthibinh@gmail.com',
                'phone_number' => '0907654321',
                'address' => '456 Nguyễn Huệ, Quận 1, TP.HCM',
                'birthday' => '1990-08-20',
                'gender' => 'female',
                'customer_group' => 'regular',
                'customer_type' => 'individual',
            ],
            [
                'customer_name' => 'Lê Văn Cường',
                'email' => 'levancuong@gmail.com',
                'phone_number' => '0912345678',
                'address' => '789 Đồng Khởi, Quận 1, TP.HCM',
                'birthday' => '1988-12-10',
                'gender' => 'male',
                'customer_group' => 'regular',
                'customer_type' => 'individual',
            ],
            [
                'customer_name' => 'Phạm Thị Dung',
                'email' => 'phamthidung@gmail.com',
                'phone_number' => '0923456789',
                'address' => '321 Hai Bà Trưng, Quận 3, TP.HCM',
                'birthday' => '1992-03-25',
                'gender' => 'female',
                'customer_group' => 'new',
                'customer_type' => 'individual',
            ],
            [
                'customer_name' => 'Hoàng Văn Em',
                'email' => 'hoangvanem@gmail.com',
                'phone_number' => '0934567890',
                'address' => '654 Cách Mạng Tháng 8, Quận 10, TP.HCM',
                'birthday' => '1987-07-18',
                'gender' => 'male',
                'customer_group' => 'vip',
                'customer_type' => 'individual',
            ],
        ];

        foreach ($stores as $storeIndex => $store) {
            foreach ($customerData as $index => $data) {
                $customerCode = 'CUST' . str_pad(($storeIndex * 100) + $index + 1, 6, '0', STR_PAD_LEFT);

                Customer::create(array_merge($data, [
                    'store_id' => $store->id,
                    'customer_code' => $customerCode,
                    'current_debt' => 0,
                    'total_sales' => rand(1000000, 50000000),
                    'total_sales_minus_returns' => rand(1000000, 50000000),
                    'last_transaction_at' => now()->subDays(rand(1, 30)),
                ]));
            }
        }
    }
}
