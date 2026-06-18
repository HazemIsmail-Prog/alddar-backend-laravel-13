<?php

namespace App\Actions\Parties;

use App\Models\Party;

final class UpdateBalanceAction
{

    public function handle(Party $party): void
    {
        $totalInvoicesAmount = $party->invoices->sum('total_amount');
        $totalPaymentsAmount = $party->payments->sum('amount');

        $party->balance = $totalInvoicesAmount - $totalPaymentsAmount;
        $party->save();
    }
}
