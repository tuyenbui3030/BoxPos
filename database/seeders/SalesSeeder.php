<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\SalesOrders\Models\SalesOrderItem;
use Packages\SalesOrders\Models\Invoice;
use Packages\SalesOrders\Models\InvoiceItem;
use Packages\CashManagement\Models\CashAccount;
use Packages\CashManagement\Models\CashCategory;
use Packages\CashManagement\Models\CashTransaction;
use Packages\Store\Models\Store;
use Packages\Customer\Models\Customer;
use Packages\Products\Models\Product;
use Packages\User\Models\User;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            // Create sales orders and invoices
            // Cash accounts and categories should be created by CashManagementSeeder
            $this->createSalesOrders($store);
        }
    }

    private function createCashAccounts($store)
    {
        $accounts = [
            [
                'account_code' => 'CASH001',
                'account_name' => 'Tiền mặt quầy thu ngân',
                'account_type' => 'cash',
                'opening_balance' => 10000000,
                'current_balance' => 15000000,
                'is_default' => true,
            ],
            [
                'account_code' => 'BANK001',
                'account_name' => 'Tài khoản Vietcombank',
                'account_type' => 'bank',
                'bank_name' => 'Vietcombank',
                'account_number' => '1234567890',
                'opening_balance' => 50000000,
                'current_balance' => 75000000,
            ],
            [
                'account_code' => 'MOMO001',
                'account_name' => 'Ví MoMo',
                'account_type' => 'e_wallet',
                'opening_balance' => 5000000,
                'current_balance' => 8000000,
            ],
        ];

        foreach ($accounts as $accountData) {
            CashAccount::create(array_merge($accountData, [
                'store_id' => $store->id,
                'opening_date' => now()->subMonths(6),
                'currency' => 'VND',
                'is_active' => true,
                'created_by' => 1,
            ]));
        }
    }

    private function createCashCategories($store)
    {
        $categories = [
            [
                'code' => 'SALES_INCOME',
                'name' => 'Doanh thu bán hàng',
                'type' => 'income',
                'color' => '#28a745',
            ],
            [
                'code' => 'SERVICE_INCOME',
                'name' => 'Doanh thu dịch vụ',
                'type' => 'income',
                'color' => '#17a2b8',
            ],
            [
                'code' => 'OPERATING_EXPENSE',
                'name' => 'Chi phí hoạt động',
                'type' => 'expense',
                'color' => '#dc3545',
            ],
            [
                'code' => 'PURCHASE_EXPENSE',
                'name' => 'Chi phí mua hàng',
                'type' => 'expense',
                'color' => '#fd7e14',
            ],
        ];

        foreach ($categories as $categoryData) {
            CashCategory::create(array_merge($categoryData, [
                'store_id' => $store->id,
                'is_active' => true,
                'sort_order' => 1,
            ]));
        }
    }

    private function createSalesOrders($store)
    {
        $customers = Customer::where('store_id', $store->id)->get();
        $products = Product::where('store_id', $store->id)->get();
        $users = User::whereHas('stores', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->get();
        $cashAccount = CashAccount::where('store_id', $store->id)->where('account_type', 'cash')->first();
        $incomeCategory = CashCategory::where('store_id', $store->id)->where('code', 'SALES')->first();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Create 20 sales orders
        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers->random();
            $orderDate = now()->subDays(rand(1, 60));
            
            $salesOrder = SalesOrder::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'order_number' => SalesOrder::generateOrderNumber($store->id),
                'order_date' => $orderDate,
                'status' => collect(['pending', 'confirmed', 'processing', 'delivered'])->random(),
                'payment_status' => collect(['pending', 'partial', 'paid'])->random(),
                'payment_method' => collect(['cash', 'bank_transfer', 'card', 'e_wallet'])->random(),
                'subtotal' => 0, // Will be calculated
                'tax_amount' => 0,
                'discount_amount' => rand(0, 500000),
                'shipping_cost' => rand(0, 100000),
                'total_amount' => 0, // Will be calculated
                'currency' => 'VND',
                'notes' => 'Đơn hàng mẫu #' . $i,
                'created_by' => $users->random()->id,
            ]);

            // Add 2-5 items to each order
            $itemCount = rand(2, 5);
            $subtotal = 0;

            for ($j = 1; $j <= $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 10);
                $unitPrice = $product->selling_price;
                $lineTotal = $quantity * $unitPrice;
                $subtotal += $lineTotal;

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'item_type' => 'product',
                    'item_id' => $product->id,
                    'item_code' => $product->sku,
                    'item_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'unit' => $product->unit ?? 'pcs',
                    'notes' => 'Item #' . $j,
                ]);
            }

            // Calculate totals
            $taxAmount = $subtotal * 0.1; // 10% tax
            $totalAmount = $subtotal + $taxAmount - $salesOrder->discount_amount + $salesOrder->shipping_cost;

            $salesOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            // Create invoice for completed orders
            if ($salesOrder->status === 'completed') {
                $invoice = Invoice::create([
                    'store_id' => $store->id,
                    'sales_order_id' => $salesOrder->id,
                    'customer_id' => $customer->id,
                    'invoice_number' => Invoice::generateInvoiceNumber($store->id),
                    'invoice_date' => $orderDate->addDays(rand(1, 3)),
                    'due_date' => $orderDate->addDays(rand(7, 30)),
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'payment_method' => $salesOrder->payment_method,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $salesOrder->discount_amount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $totalAmount,
                    'currency' => 'VND',
                    'notes' => 'Hóa đơn cho đơn hàng ' . $salesOrder->order_number,
                    'created_by' => $salesOrder->created_by,
                ]);

                // Copy items to invoice
                foreach ($salesOrder->items as $orderItem) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $orderItem->product_id,
                        'product_code' => $orderItem->product_code,
                        'product_name' => $orderItem->product_name,
                        'quantity' => $orderItem->quantity,
                        'unit_price' => $orderItem->unit_price,
                        'line_total' => $orderItem->line_total,
                        'unit' => $orderItem->unit,
                        'tax_rate' => 10,
                        'tax_amount' => $orderItem->line_total * 0.1,
                    ]);
                }

                // Create cash transaction for payment
                if ($cashAccount && $incomeCategory) {
                    CashTransaction::create([
                        'store_id' => $store->id,
                        'account_id' => $cashAccount->id,
                        'category_id' => $incomeCategory->id,
                        'transaction_number' => CashTransaction::generateTransactionNumber($store->id),
                        'transaction_date' => $invoice->invoice_date,
                        'type' => 'income',
                        'amount' => $totalAmount,
                        'currency' => 'VND',
                        'base_amount' => $totalAmount,
                        'payment_method' => $salesOrder->payment_method,
                        'payer_payee' => $customer->name,
                        'related_document_type' => 'invoice',
                        'related_document_id' => $invoice->id,
                        'related_document_number' => $invoice->invoice_number,
                        'status' => 'completed',
                        'description' => 'Thanh toán hóa đơn ' . $invoice->invoice_number,
                        'created_by' => $invoice->created_by,
                    ]);
                }
            }
        }
    }
}
