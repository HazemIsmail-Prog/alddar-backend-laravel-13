<?php

namespace App\Actions;

use App\Models\Order;

class UpdateOrderPaidAmountAndPaymentStatusAction
{

    public function handle(Order $order): void
    {
        $totalPaidAmount = $order->invoices->sum('amount_paid');
        $order->amount_paid = $totalPaidAmount;
        $order->payment_status = $totalPaidAmount >= $order->total_amount ? 'paid' : 'partially_paid';
        $order->save();
    }
}
