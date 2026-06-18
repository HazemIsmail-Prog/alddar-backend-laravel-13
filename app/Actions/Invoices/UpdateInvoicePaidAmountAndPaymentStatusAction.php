<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;

final class UpdateInvoicePaidAmountAndPaymentStatusAction
{

    public function handle(Invoice $invoice): void
    {
        $totalPaidAmount = $invoice->payments->sum('amount');

        $invoice->amount_paid = $totalPaidAmount;
        $invoice->status = $totalPaidAmount >= $invoice->total_amount ? 'paid' : 'partially_paid';
        $invoice->save();
    }
}
