<?php

namespace App\Actions;

use App\Models\Party;

class UpdatePartyBalanceAction
{

    public function handle(Party $party): void
    {
        $totalInvoicesAmount = $party->invoices->sum('total_amount');
        $totalPaymentsAmount = $party->payments->sum('amount');

        $party->balance = $totalInvoicesAmount - $totalPaymentsAmount;
        $party->save();
    }
}
