<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\SalesOrders\Models\SalesOrder;
use Packages\SalesOrders\Models\Invoice;
use Packages\SalesOrders\Models\InvoiceItem;
use Packages\User\Models\User;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧾 Seeding Invoices...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createInvoicesForStore($store);
        }

        $this->command->info('✅ Invoices seeded successfully!');
    }

    private function createInvoicesForStore(Store $store): void
    {
        $salesOrders = SalesOrder::where('store_id', $store->id)
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        if ($salesOrders->isEmpty()) {
            return;
        }

        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        foreach ($salesOrders as $salesOrder) {
            // 80% chance to create invoice for each sales order
            if (rand(1, 100) <= 80) {
                $this->createInvoiceForSalesOrder($salesOrder, $createdBy);
            }
        }
    }

    private function createInvoiceForSalesOrder(SalesOrder $salesOrder, ?User $createdBy): void
    {
        // Check if invoice already exists for this sales order
        $existingInvoice = Invoice::where('sales_order_id', $salesOrder->id)->first();
        if ($existingInvoice) {
            return; // Skip if already exists
        }

        $invoiceDate = $this->getInvoiceDate($salesOrder);
        $dueDate = $invoiceDate->copy()->addDays($this->getPaymentTermsDays($salesOrder));

        $invoiceNumber = $this->generateInvoiceNumber($salesOrder, $invoiceDate);
        
        $invoice = Invoice::create([
            'store_id' => $salesOrder->store_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'invoice_type' => $this->getInvoiceType($salesOrder),
            'status' => $this->getInvoiceStatus($invoiceDate, $dueDate),
            'subtotal' => 0, // Will be calculated after items
            'tax_amount' => 0,
            'discount_amount' => $salesOrder->discount_amount,
            'shipping_cost' => $salesOrder->shipping_cost,
            'other_charges' => $salesOrder->other_charges,
            'total_amount' => 0, // Will be calculated after items
            'currency' => $salesOrder->currency,
            'payment_status' => $this->getPaymentStatus($invoiceDate, $salesOrder),
            'payment_method' => $salesOrder->payment_method,
            'paid_amount' => 0, // Will be set based on payment status
            'balance_due' => 0,
            'billing_address' => $this->getBillingAddress($salesOrder),
            'shipping_address' => $salesOrder->delivery_address,
            'notes' => $this->generateInvoiceNotes($salesOrder),
            'sales_person_id' => $salesOrder->sales_person_id,
            'created_by' => $createdBy?->id,
            'approved_by' => $createdBy?->id,
            'approved_at' => $invoiceDate->copy()->addHours(rand(1, 4)),
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'source' => 'sales_order',
                'auto_generated' => true,
            ]),
        ]);

        // Create invoice items from sales order items
        $this->createInvoiceItems($invoice, $salesOrder);

        // Update invoice totals
        $this->updateInvoiceTotals($invoice);
    }

    private function createInvoiceItems(Invoice $invoice, SalesOrder $salesOrder): void
    {
        foreach ($salesOrder->items as $orderItem) {
            // Determine quantity to invoice (could be partial)
            $quantityToInvoice = $this->getQuantityToInvoice($orderItem);
            
            if ($quantityToInvoice <= 0) {
                continue;
            }

            $unitPrice = $orderItem->unit_price;
            $lineTotal = $quantityToInvoice * $unitPrice;
            $discountAmount = $lineTotal * ($orderItem->discount_percentage / 100);
            $netAmount = $lineTotal - $discountAmount;
            $taxAmount = $netAmount * ($orderItem->tax_rate / 100);
            $totalAmount = $netAmount + $taxAmount;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'sales_order_item_id' => $orderItem->id,
                'item_type' => $orderItem->item_type,
                'item_id' => $orderItem->item_id,
                'item_code' => $orderItem->item_code,
                'item_name' => $orderItem->item_name,
                'item_description' => $orderItem->notes,
                'quantity' => $quantityToInvoice,
                'unit' => $orderItem->unit,
                'unit_price' => $unitPrice,
                'discount_percent' => $orderItem->discount_percentage ?? 0,
                'discount_amount' => $discountAmount,
                'tax_percent' => $orderItem->tax_rate ?? 0,
                'tax_amount' => $taxAmount,
                'line_total' => $totalAmount,
                'notes' => $this->generateItemNotes($orderItem, $quantityToInvoice),
                'metadata' => json_encode([
                    'original_order_quantity' => $orderItem->quantity,
                    'invoiced_quantity' => $quantityToInvoice,
                    'remaining_quantity' => $orderItem->quantity - $quantityToInvoice,
                ]),
            ]);
        }
    }

    private function updateInvoiceTotals(Invoice $invoice): void
    {
        $items = $invoice->items;
        $subtotal = $items->sum('net_amount');
        $taxAmount = $items->sum('tax_amount');
        $totalAmount = $subtotal + $taxAmount + $invoice->shipping_cost + $invoice->other_charges - $invoice->discount_amount;

        $paidAmount = $this->calculatePaidAmount($invoice->payment_status, $totalAmount);
        $balanceDue = $totalAmount - $paidAmount;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
        ]);
    }

    private function getInvoiceDate(SalesOrder $salesOrder): Carbon
    {
        // Invoice date is usually 1-3 days after order date
        return Carbon::parse($salesOrder->order_date)->addDays(rand(1, 3));
    }

    private function getPaymentTermsDays(SalesOrder $salesOrder): int
    {
        // Payment terms based on customer type and order amount
        if ($salesOrder->total_amount > 50000000) { // > 50M VND
            return 30; // Net 30
        } elseif ($salesOrder->total_amount > 20000000) { // > 20M VND
            return 15; // Net 15
        } else {
            return 7; // Net 7
        }
    }

    private function generateInvoiceNumber(SalesOrder $salesOrder, Carbon $invoiceDate): string
    {
        $storeCode = strtoupper(substr($salesOrder->store->slug, 0, 3));
        $dateCode = $invoiceDate->format('Ymd');
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return "INV-{$storeCode}-{$dateCode}-{$sequence}";
    }

    private function getInvoiceType(SalesOrder $salesOrder): string
    {
        // Available types: 'sale', 'return', 'credit_note', 'debit_note'
        if ($salesOrder->status === 'returned') {
            return 'credit_note';
        } else {
            return 'sale';
        }
    }

    private function getInvoiceStatus(Carbon $invoiceDate, Carbon $dueDate): string
    {
        $now = Carbon::now();
        
        if ($invoiceDate->isFuture()) {
            return 'draft';
        } elseif ($invoiceDate->isToday() || $invoiceDate->isYesterday()) {
            return 'sent';
        } elseif ($dueDate->isFuture()) {
            return 'viewed';
        } elseif ($dueDate->isPast()) {
            return rand(0, 1) == 1 ? 'paid' : 'overdue';
        } else {
            return 'sent';
        }
    }

    private function getPaymentStatus(Carbon $invoiceDate, SalesOrder $salesOrder): string
    {
        $daysSinceInvoice = $invoiceDate->diffInDays(Carbon::now());
        
        if ($daysSinceInvoice < 1) {
            return 'pending';
        } elseif ($daysSinceInvoice < 7) {
            return rand(0, 1) == 1 ? 'pending' : 'partial';
        } else {
            $rand = rand(1, 100);
            if ($rand <= 60) return 'paid';
            if ($rand <= 80) return 'partial';
            if ($rand <= 95) return 'pending';
            return 'cancelled';
        }
    }

    private function getBillingAddress(SalesOrder $salesOrder): ?string
    {
        if ($salesOrder->customer) {
            return $salesOrder->customer->address;
        }
        
        return $salesOrder->delivery_address;
    }

    private function getPaymentTerms(SalesOrder $salesOrder): string
    {
        $paymentTerms = [
            'Net 7 days',
            'Net 15 days', 
            'Net 30 days',
            'Due on receipt',
            '2/10 Net 30',
        ];
        
        return $paymentTerms[array_rand($paymentTerms)];
    }

    private function generateInvoiceNotes(SalesOrder $salesOrder): ?string
    {
        $notes = [];
        
        if ($salesOrder->priority === 'urgent') {
            $notes[] = 'Đơn hàng khẩn cấp';
        }
        
        if ($salesOrder->delivery_method === 'delivery') {
            $notes[] = 'Bao gồm phí giao hàng';
        }
        
        if ($salesOrder->discount_amount > 0) {
            $notes[] = 'Đã áp dụng chiết khấu';
        }
        
        $notes[] = 'Cảm ơn quý khách đã tin tưởng sử dụng dịch vụ';
        
        return implode('. ', $notes);
    }

    private function getQuantityToInvoice($orderItem): float
    {
        // Most items are fully invoiced, some are partial
        $rand = rand(1, 100);
        
        if ($rand <= 80) {
            // Full quantity
            return $orderItem->quantity;
        } elseif ($rand <= 95) {
            // Partial quantity (70-90%)
            return $orderItem->quantity * (rand(70, 90) / 100);
        } else {
            // No invoice for this item
            return 0;
        }
    }

    private function generateItemNotes($orderItem, float $quantityInvoiced): ?string
    {
        if ($quantityInvoiced < $orderItem->quantity) {
            $remaining = $orderItem->quantity - $quantityInvoiced;
            return "Giao hàng một phần. Còn lại: {$remaining} đơn vị";
        }
        
        return null;
    }

    private function calculatePaidAmount(string $paymentStatus, float $totalAmount): float
    {
        return match($paymentStatus) {
            'paid' => $totalAmount,
            'partial' => $totalAmount * (rand(30, 70) / 100),
            'overdue' => $totalAmount * (rand(0, 30) / 100),
            default => 0,
        };
    }
}
