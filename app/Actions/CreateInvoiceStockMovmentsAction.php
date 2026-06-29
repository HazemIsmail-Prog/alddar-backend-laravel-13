<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Services\StockMovementService;

class CreateInvoiceStockMovmentsAction
{

    public function handle(Invoice $invoice): void
    {
        // get the invoice items
        $invoiceItems = $invoice->items->toArray();

        // create stock movements for the invoice items where has warehouse id and product id
        $movement_type = match ($invoice->invoice_type) {
            'purchase' => 'in',
            'sales' => 'out',
            'credit_note' => 'out',
            'debit_note' => 'in',
            default => throw new \Exception('Invalid invoice type'),
        };

        $transaction_type = match ($invoice->invoice_type) {
            'purchase' => 'purchase',
            'sales' => 'sale',
            'credit_note' => 'return',
            'debit_note' => 'return',
            default => throw new \Exception('Invalid invoice type'),
        };

        $stockMovementsData = StockMovementService::getInvoiceStockMovementsData($invoiceItems, $movement_type, $transaction_type);

        if (!empty($stockMovementsData)) {

            $invoice->stockMovements()->delete();

            $invoice->stockMovements()->createMany($stockMovementsData);

        }
    }
}