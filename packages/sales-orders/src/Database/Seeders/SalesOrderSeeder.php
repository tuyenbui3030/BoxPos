<?php

namespace Packages\SalesOrders\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\SalesOrders\Models\SalesOrderItem;
use Packages\SalesOrders\Models\Invoice;
use Packages\SalesOrders\Models\InvoiceItem;
use Packages\SalesOrders\Models\SalesReturn;
use Packages\SalesOrders\Models\SalesReturnItem;
use Packages\Customer\Models\Customer;
use Packages\Products\Models\Product;
use Packages\Products\Models\Service;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class SalesOrderSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedSalesOrders();
            $this->seedInvoices();
            $this->seedSalesReturns();
        });
    }

    /**
     * Seed sales orders for all stores
     */
    private function seedSalesOrders(): void
    {
        $this->logSeedingProgress('sales_orders_seeding_started');

        $stores = $this->getStores();
        $ordersPerCustomer = $this->getConfigValue('orders_per_customer', 5);

        foreach ($stores as $store) {
            $this->seedSalesOrdersForStore($store, $ordersPerCustomer);
        }

        $this->logSeedingProgress('sales_orders_seeding_completed');
    }

    /**
     * Seed sales orders for a specific store
     */
    private function seedSalesOrdersForStore(Store $store, int $ordersPerCustomer): void
    {
        $this->logSeedingProgress('seeding_sales_orders_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'orders_per_customer' => $ordersPerCustomer
        ]);

        $customers = Customer::where('store_id', $store->id)->get();
        $users = User::all();
        $products = Product::where('store_id', $store->id)->where('status', 'active')->get();
        $services = Service::where('store_id', $store->id)->where('status', 'active')->get();

        if ($customers->isEmpty()) {
            $this->logSeedingProgress('no_customers_found_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);
            return;
        }

        foreach ($customers as $customer) {
            $orderCount = fake()->numberBetween(1, $ordersPerCustomer);
            
            for ($i = 0; $i < $orderCount; $i++) {
                $salesOrder = $this->createSalesOrder($store, $customer, $users);
                $this->createSalesOrderItems($salesOrder, $products, $services);
                $this->updateSalesOrderTotals($salesOrder);
            }
        }
    }

    /**
     * Create a sales order
     */
    private function createSalesOrder(Store $store, Customer $customer, $users): SalesOrder
    {
        $orderDate = fake()->dateTimeBetween('-6 months', 'now');
        $deliveryDate = fake()->dateTimeBetween($orderDate, '+1 month');
        
        return SalesOrder::create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'order_number' => SalesOrder::generateOrderNumber($store->id),
            'customer_reference' => fake()->optional()->regexify('[A-Z]{2}[0-9]{4}'),
            'order_date' => $orderDate,
            'delivery_date' => $deliveryDate,
            'actual_delivery_date' => fake()->boolean(60) ? fake()->dateTimeBetween($orderDate, 'now') : null,
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'status' => fake()->randomElement(['confirmed', 'processing', 'shipped', 'delivered']),
            'order_type' => fake()->randomElement(['sale', 'quote']),
            'currency' => 'VND',
            'payment_status' => fake()->randomElement(['pending', 'partial', 'paid']),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'card']),
            'payment_due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'delivery_address' => $customer->address ?: $this->getVietnameseAddress(),
            'delivery_contact' => $customer->customer_name,
            'delivery_phone' => $customer->phone_number,
            'delivery_instructions' => fake()->optional()->sentence(),
            'delivery_method' => fake()->randomElement(['pickup', 'delivery', 'shipping']),
            'tracking_number' => fake()->optional()->regexify('[A-Z]{2}[0-9]{10}'),
            'sales_channel' => fake()->randomElement(['in_store', 'phone', 'online']),
            'sales_person_id' => $users->isNotEmpty() ? $users->random()->id : null,
            'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
            'approved_by' => fake()->boolean(80) ? ($users->isNotEmpty() ? $users->random()->id : null) : null,
            'approved_at' => fake()->boolean(80) ? fake()->dateTimeBetween($orderDate, 'now') : null,
            'notes' => fake()->optional()->paragraph(),
            'internal_notes' => fake()->optional()->sentence(),
            'metadata' => [],
            'is_recurring' => fake()->boolean(5),
            'recurring_frequency' => fake()->boolean(5) ? fake()->randomElement(['monthly', 'quarterly']) : null,
        ]);
    }

    /**
     * Create sales order items
     */
    private function createSalesOrderItems(SalesOrder $salesOrder, $products, $services): void
    {
        $itemCount = fake()->numberBetween(1, $this->getConfigValue('order_items_per_order', 3));
        
        for ($i = 0; $i < $itemCount; $i++) {
            // 70% products, 30% services
            if (fake()->boolean(70) && $products->isNotEmpty()) {
                $this->createProductOrderItem($salesOrder, $products->random());
            } elseif ($services->isNotEmpty()) {
                $this->createServiceOrderItem($salesOrder, $services->random());
            }
        }
    }

    /**
     * Create a product order item
     */
    private function createProductOrderItem(SalesOrder $salesOrder, Product $product): void
    {
        $quantity = fake()->randomFloat(2, 1, 20);
        $unitPrice = $product->selling_price;
        $discountPercent = fake()->boolean(20) ? fake()->randomFloat(2, 0, 15) : 0;
        $discountAmount = ($quantity * $unitPrice) * ($discountPercent / 100);
        $taxPercent = $product->tax_rate ?: 10;
        $subtotal = ($quantity * $unitPrice) - $discountAmount;
        $taxAmount = $subtotal * ($taxPercent / 100);
        $lineTotal = $subtotal + $taxAmount;
        
        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'item_type' => 'product',
            'item_id' => $product->id,
            'item_code' => $product->sku,
            'item_name' => $product->name,
            'item_description' => $product->short_description,
            'quantity' => $quantity,
            'delivered_quantity' => fake()->boolean(60) ? $quantity : fake()->randomFloat(2, 0, $quantity),
            'returned_quantity' => 0,
            'unit' => $product->unit,
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'unit_cost' => $product->cost_price,
            'total_cost' => $quantity * $product->cost_price,
            'batch_number' => fake()->optional()->regexify('BATCH[0-9]{6}'),
            'serial_numbers' => fake()->optional()->regexify('SN[0-9]{8}'),
            'expiry_date' => fake()->optional()->dateTimeBetween('+1 month', '+2 years'),
            'status' => $salesOrder->status === 'delivered' ? 'delivered' : fake()->randomElement(['confirmed', 'processing', 'delivered']),
            'notes' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Create a service order item
     */
    private function createServiceOrderItem(SalesOrder $salesOrder, Service $service): void
    {
        $quantity = fake()->randomFloat(2, 1, 5); // Services usually have lower quantities
        $unitPrice = $service->base_price;
        $discountPercent = fake()->boolean(15) ? fake()->randomFloat(2, 0, 10) : 0;
        $discountAmount = ($quantity * $unitPrice) * ($discountPercent / 100);
        $taxPercent = $service->tax_rate ?: 10;
        $subtotal = ($quantity * $unitPrice) - $discountAmount;
        $taxAmount = $subtotal * ($taxPercent / 100);
        $lineTotal = $subtotal + $taxAmount;
        
        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'item_type' => 'service',
            'item_id' => $service->id,
            'item_code' => $service->code,
            'item_name' => $service->name,
            'item_description' => $service->short_description,
            'quantity' => $quantity,
            'delivered_quantity' => fake()->boolean(70) ? $quantity : fake()->randomFloat(2, 0, $quantity),
            'returned_quantity' => 0,
            'unit' => 'lần',
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'unit_cost' => $unitPrice * 0.7, // Assume 70% cost ratio for services
            'total_cost' => $quantity * ($unitPrice * 0.7),
            'batch_number' => null,
            'serial_numbers' => null,
            'expiry_date' => null,
            'status' => $salesOrder->status === 'delivered' ? 'delivered' : fake()->randomElement(['confirmed', 'processing', 'delivered']),
            'notes' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Update sales order totals based on items
     */
    private function updateSalesOrderTotals(SalesOrder $salesOrder): void
    {
        $items = $salesOrder->items;
        
        $subtotal = $items->sum('line_total') - $items->sum('tax_amount');
        $taxAmount = $items->sum('tax_amount');
        $discountAmount = fake()->boolean(20) ? $subtotal * 0.05 : 0; // Order-level discount
        $shippingCost = fake()->randomFloat(2, 0, 200);
        $otherCharges = fake()->randomFloat(2, 0, 100);
        $totalAmount = $subtotal + $taxAmount + $shippingCost + $otherCharges - $discountAmount;
        
        // Determine payment amounts based on payment status
        $paidAmount = match($salesOrder->payment_status) {
            'paid' => $totalAmount,
            'partial' => fake()->randomFloat(2, $totalAmount * 0.3, $totalAmount * 0.8),
            default => 0
        };
        
        $salesOrder->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'shipping_cost' => $shippingCost,
            'other_charges' => $otherCharges,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $totalAmount - $paidAmount,
        ]);
    }

    /**
     * Seed invoices for sales orders
     */
    private function seedInvoices(): void
    {
        $this->logSeedingProgress('invoices_seeding_started');

        // Create invoices for 80% of completed/delivered orders
        $salesOrders = SalesOrder::whereIn('status', ['delivered', 'completed'])
            ->with('items')
            ->get();

        foreach ($salesOrders as $salesOrder) {
            if (fake()->boolean(80)) { // 80% of orders get invoiced
                $invoice = $this->createInvoice($salesOrder);
                $this->createInvoiceItems($invoice, $salesOrder);
                $this->updateInvoiceTotals($invoice);
            }
        }

        $this->logSeedingProgress('invoices_seeding_completed');
    }

    /**
     * Create an invoice for a sales order
     */
    private function createInvoice(SalesOrder $salesOrder): Invoice
    {
        $invoiceDate = fake()->dateTimeBetween($salesOrder->order_date, 'now');
        $dueDate = fake()->dateTimeBetween($invoiceDate, '+30 days');
        
        return Invoice::create([
            'store_id' => $salesOrder->store_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'invoice_number' => Invoice::generateInvoiceNumber($salesOrder->store_id),
            'tax_invoice_number' => fake()->optional()->regexify('TAX[0-9]{8}'),
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'invoice_type' => 'sale',
            'status' => fake()->randomElement(['sent', 'viewed', 'paid', 'overdue']),
            'currency' => 'VND',
            'payment_status' => $salesOrder->payment_status,
            'payment_method' => $salesOrder->payment_method,
            'customer_name' => $salesOrder->customer->customer_name,
            'customer_email' => $salesOrder->customer->email,
            'customer_phone' => $salesOrder->customer->phone_number,
            'billing_address' => $salesOrder->customer->address,
            'shipping_address' => $salesOrder->delivery_address,
            'customer_tax_code' => fake()->optional()->regexify('[0-9]{10}'),
            'tax_rate' => 10,
            'is_tax_inclusive' => false,
            'is_e_invoice' => fake()->boolean(30),
            'e_invoice_code' => fake()->boolean(30) ? fake()->regexify('E[0-9]{10}') : null,
            'e_invoice_sent_at' => fake()->boolean(30) ? fake()->dateTimeBetween($invoiceDate, 'now') : null,
            'e_invoice_data' => [],
            'sales_person_id' => $salesOrder->sales_person_id,
            'created_by' => $salesOrder->created_by,
            'approved_by' => fake()->boolean(90) ? $salesOrder->approved_by : null,
            'approved_at' => fake()->boolean(90) ? fake()->dateTimeBetween($invoiceDate, 'now') : null,
            'notes' => fake()->optional()->paragraph(),
            'terms_conditions' => 'Thanh toán trong vòng 30 ngày kể từ ngày xuất hóa đơn.',
            'metadata' => [],
        ]);
    }

    /**
     * Create invoice items from sales order items
     */
    private function createInvoiceItems(Invoice $invoice, SalesOrder $salesOrder): void
    {
        foreach ($salesOrder->items as $orderItem) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'sales_order_item_id' => $orderItem->id,
                'item_type' => $orderItem->item_type,
                'item_id' => $orderItem->item_id,
                'item_code' => $orderItem->item_code,
                'item_name' => $orderItem->item_name,
                'item_description' => $orderItem->item_description,
                'quantity' => $orderItem->delivered_quantity ?: $orderItem->quantity,
                'unit' => $orderItem->unit,
                'unit_price' => $orderItem->unit_price,
                'discount_percent' => $orderItem->discount_percent,
                'discount_amount' => $orderItem->discount_amount,
                'tax_percent' => $orderItem->tax_percent,
                'tax_amount' => $orderItem->tax_amount,
                'line_total' => $orderItem->line_total,
                'unit_cost' => $orderItem->unit_cost,
                'total_cost' => $orderItem->total_cost,
                'batch_number' => $orderItem->batch_number,
                'serial_numbers' => $orderItem->serial_numbers,
                'expiry_date' => $orderItem->expiry_date,
                'notes' => $orderItem->notes,
                'metadata' => [],
            ]);
        }
    }

    /**
     * Update invoice totals based on items
     */
    private function updateInvoiceTotals(Invoice $invoice): void
    {
        $items = $invoice->items;
        
        $subtotal = $items->sum('line_total') - $items->sum('tax_amount');
        $taxAmount = $items->sum('tax_amount');
        $discountAmount = fake()->boolean(15) ? $subtotal * 0.03 : 0;
        $shippingCost = fake()->randomFloat(2, 0, 150);
        $otherCharges = fake()->randomFloat(2, 0, 50);
        $totalAmount = $subtotal + $taxAmount + $shippingCost + $otherCharges - $discountAmount;
        
        $paidAmount = match($invoice->payment_status) {
            'paid' => $totalAmount,
            'partial' => fake()->randomFloat(2, $totalAmount * 0.4, $totalAmount * 0.9),
            default => 0
        };
        
        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'shipping_cost' => $shippingCost,
            'other_charges' => $otherCharges,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $totalAmount - $paidAmount,
        ]);
    }

    /**
     * Seed sales returns
     */
    private function seedSalesReturns(): void
    {
        $this->logSeedingProgress('sales_returns_seeding_started');

        // Create returns for 10% of delivered orders
        $salesOrders = SalesOrder::where('status', 'delivered')
            ->with(['items', 'invoices'])
            ->get();

        foreach ($salesOrders as $salesOrder) {
            if (fake()->boolean(10)) { // 10% return rate
                $salesReturn = $this->createSalesReturn($salesOrder);
                $this->createSalesReturnItems($salesReturn, $salesOrder);
                $this->updateSalesReturnTotals($salesReturn);
            }
        }

        $this->logSeedingProgress('sales_returns_seeding_completed');
    }

    /**
     * Create a sales return
     */
    private function createSalesReturn(SalesOrder $salesOrder): SalesReturn
    {
        $invoice = $salesOrder->invoices->first();
        $returnDate = fake()->dateTimeBetween($salesOrder->order_date, 'now');
        $users = User::all();
        
        return SalesReturn::create([
            'store_id' => $salesOrder->store_id,
            'sales_order_id' => $salesOrder->id,
            'invoice_id' => $invoice?->id,
            'customer_id' => $salesOrder->customer_id,
            'return_number' => SalesReturn::generateReturnNumber($salesOrder->store_id),
            'return_date' => $returnDate,
            'return_type' => fake()->randomElement(['partial_return', 'full_return']),
            'status' => fake()->randomElement(['approved', 'processed', 'refunded']),
            'return_reason' => fake()->randomElement(['defective', 'wrong_item', 'damaged_shipping', 'other']),
            'return_reason_detail' => $this->getVietnameseReturnReason(),
            'currency' => 'VND',
            'refund_method' => fake()->randomElement(['cash', 'bank_transfer', 'card']),
            'refund_status' => fake()->randomElement(['processed', 'completed']),
            'refund_processed_date' => fake()->boolean(70) ? fake()->dateTimeBetween($returnDate, 'now') : null,
            'restock_items' => fake()->boolean(60),
            'item_condition' => fake()->randomElement(['good', 'fair', 'damaged']),
            'processed_by' => $users->isNotEmpty() ? $users->random()->id : null,
            'approved_by' => fake()->boolean(90) ? ($users->isNotEmpty() ? $users->random()->id : null) : null,
            'approved_at' => fake()->boolean(90) ? fake()->dateTimeBetween($returnDate, 'now') : null,
            'notes' => fake()->optional()->paragraph(),
            'internal_notes' => fake()->optional()->sentence(),
            'attachments' => [],
            'metadata' => [],
        ]);
    }

    /**
     * Create sales return items
     */
    private function createSalesReturnItems(SalesReturn $salesReturn, SalesOrder $salesOrder): void
    {
        $orderItems = $salesOrder->items;
        $returnItemCount = fake()->numberBetween(1, min(3, $orderItems->count()));
        $selectedItems = $orderItems->random($returnItemCount);
        
        foreach ($selectedItems as $orderItem) {
            $invoice = $salesReturn->invoice;
            $invoiceItem = $invoice ? $invoice->items()->where('sales_order_item_id', $orderItem->id)->first() : null;
            
            $originalQuantity = $orderItem->delivered_quantity ?: $orderItem->quantity;
            $returnQuantity = fake()->randomFloat(2, 1, $originalQuantity);
            
            SalesReturnItem::create([
                'sales_return_id' => $salesReturn->id,
                'sales_order_item_id' => $orderItem->id,
                'invoice_item_id' => $invoiceItem?->id,
                'item_type' => $orderItem->item_type,
                'item_id' => $orderItem->item_id,
                'item_code' => $orderItem->item_code,
                'item_name' => $orderItem->item_name,
                'item_description' => $orderItem->item_description,
                'original_quantity' => $originalQuantity,
                'return_quantity' => $returnQuantity,
                'unit' => $orderItem->unit,
                'original_unit_price' => $orderItem->unit_price,
                'return_unit_price' => $orderItem->unit_price,
                'line_total' => $returnQuantity * $orderItem->unit_price,
                'return_reason' => $salesReturn->return_reason,
                'return_reason_detail' => $salesReturn->return_reason_detail,
                'item_condition' => $salesReturn->item_condition,
                'can_restock' => $salesReturn->restock_items && in_array($salesReturn->item_condition, ['good', 'fair']),
                'batch_number' => $orderItem->batch_number,
                'serial_numbers' => $orderItem->serial_numbers,
                'expiry_date' => $orderItem->expiry_date,
                'notes' => fake()->optional()->sentence(),
                'metadata' => [],
            ]);
        }
    }

    /**
     * Update sales return totals
     */
    private function updateSalesReturnTotals(SalesReturn $salesReturn): void
    {
        $items = $salesReturn->items;
        
        $subtotal = $items->sum('line_total');
        $taxAmount = $subtotal * 0.1; // 10% tax
        $totalAmount = $subtotal + $taxAmount;
        $restockingFee = fake()->boolean(20) ? $totalAmount * 0.1 : 0;
        $refundAmount = $totalAmount - $restockingFee;
        
        $salesReturn->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'restocking_fee' => $restockingFee,
            'refund_amount' => $refundAmount,
        ]);
    }

    /**
     * Get Vietnamese addresses
     */
    private function getVietnameseAddress(): string
    {
        $addresses = [
            '123 Nguyễn Huệ, Quận 1, TP.HCM',
            '456 Lê Lợi, Quận 3, TP.HCM',
            '789 Trần Hưng Đạo, Quận 5, TP.HCM',
            '321 Võ Văn Tần, Quận 3, TP.HCM',
            '654 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM'
        ];
        
        return fake()->randomElement($addresses);
    }

    /**
     * Get Vietnamese return reasons
     */
    private function getVietnameseReturnReason(): string
    {
        $reasons = [
            'Sản phẩm bị lỗi kỹ thuật',
            'Giao sai hàng',
            'Hàng bị hư hỏng trong vận chuyển',
            'Chất lượng không đạt yêu cầu',
            'Kích thước không phù hợp',
            'Sản phẩm không đúng thông số kỹ thuật'
        ];
        
        return fake()->randomElement($reasons);
    }
}