<?php

namespace App\Actions\ChartOfAccounts;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;

class UpdateLeafAccountsBalanceAction
{
    public function handle()
    {
        // reset all chart of accounts current balance to 0
        ChartOfAccount::query()->update(['current_balance' => 0]);

        $entries = JournalEntry::selectRaw('account_id, entry_type, SUM(amount) as amount')
            ->groupBy('account_id', 'entry_type')
            ->get();

        $transformedEntries = [];
        foreach ($entries as $entry) {
            $transformedEntries[$entry->account_id] = [
                'debit' => $entry->entry_type === 'debit' ? $entry->amount : 0,
                'credit' => $entry->entry_type === 'credit' ? $entry->amount : 0,
            ];
        }

        $leafAccounts = ChartOfAccount::whereIn('id', $entries->pluck('account_id'))->get();

        $leafAccountsData = [];

        foreach ($leafAccounts as $account) {
            $normalBalance = $account->normal_balance;
            $currentBalance = 0;
            if($normalBalance === 'debit') {
                $currentBalance = $transformedEntries[$account->id]['debit'] - $transformedEntries[$account->id]['credit'];
            } else {
                $currentBalance = $transformedEntries[$account->id]['credit'] - $transformedEntries[$account->id]['debit'];
            }
            $leafAccountsData[] = [
                ...$account->toArray(),
                'created_at' => $account->created_at,  // important to get carbon instance
                'updated_at' => $account->updated_at,  // important to get carbon instance
                'current_balance' => $currentBalance * 1000, // * 1000 to convert because im using upsert
            ];
        }

        // update leaf accounts current balance
        ChartOfAccount::upsert(
            $leafAccountsData, 
            uniqueBy: ['id'], 
            update: [ 'current_balance']
        );


    }
}