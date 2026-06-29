<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\ChartOfAccount;

class UpdateOrCreateInvoiceJournalAction
{

    public function handle(Invoice $invoice): void
    {

        $journal = $invoice->journals()->first();

        if (!$journal) {

            // create new journal

            switch ($invoice->invoice_type) {
                case 'sales':
                    $journalType = 'sales';
                    $journalPostFix = 'INV';
                    break;
                case 'purchase':
                    $journalType = 'purchase';
                    $journalPostFix = 'BILL';
                    break;
                case 'credit_note':
                    $journalType = 'credit_note';
                    $journalPostFix = 'CN';
                    break;
                case 'debit_note':
                    $journalType = 'debit_note';
                    $journalPostFix = 'DN';
                    break;
                default:
                    throw new \Exception('Invalid invoice type');
            }
    
            $journalNumber = $journalPostFix.'-'.now()->format('YmdHis').random_int(1000, 9999);
    
            $journal = $invoice->journals()->create([
                'journal_number' => $journalNumber,
                'journal_type' => $journalType,
                'journal_date' => $invoice->invoice_date,
                'reference_number' => $invoice->invoice_number,
                'description' => 'Invoice ' . $invoice->id,
                'total_debit' => $invoice->total_amount + $invoice->discount_amount,
                'total_credit' => $invoice->total_amount + $invoice->discount_amount,
            ]);

        } else {

            // update existing journal
            $journal->update([
                'total_debit' => $invoice->total_amount + $invoice->discount_amount,
                'total_credit' => $invoice->total_amount + $invoice->discount_amount,
            ]);

            $journal->entries()->delete();
        }

        $entries = [];

        foreach ($invoice->items as $item) {
            $entry_type = $invoice->invoice_type === 'sales' ? 'credit' : 'debit';
            $entries[] = [
                'account_id' => $item->account_id,
                'entry_type' => $entry_type,
                'amount' => $item->total_price,
                'description' => 'Invoice ' . $invoice->id,
                'reference_type' => $invoice->getMorphClass(),
                'reference_id' => $invoice->id,
            ];
        }

        $accounts_receivable_account_id = ChartOfAccount::where('account_code', '1200')->first()->id;
        $sales_discount_account_id = ChartOfAccount::where('account_code', '4300')->first()->id;
        $payable_account_id = ChartOfAccount::where('account_code', '2110')->first()->id;
        $purchases_discount_account_id = ChartOfAccount::where('account_code', '6120')->first()->id;

        if ($invoice->invoice_type === 'sales') {

        $entries[] = [
            'account_id' => $accounts_receivable_account_id,
            'entry_type' => 'debit',
            'amount' => $invoice->total_amount,  // this is the net amount of the invoice after discount so no need to subtract discount amount
            'description' => 'Invoice ' . $invoice->id,
            'reference_type' => $invoice->getMorphClass(),
            'reference_id' => $invoice->id,
        ];

        if ($invoice->discount_amount > 0) {
            $entries[] = [
                'account_id' => $sales_discount_account_id,
                'entry_type' => 'debit',
                'amount' => $invoice->discount_amount,
                'description' => 'Invoice discount',
                'reference_type' => $invoice->getMorphClass(),
                'reference_id' => $invoice->id,
            ];
        }
        
        } else if ($invoice->invoice_type === 'purchase') {

            $entries[] = [
                'account_id' => $payable_account_id,
                'entry_type' => 'credit',
                'amount' => $invoice->total_amount,
                'description' => 'Invoice ' . $invoice->id,
                'reference_type' => $invoice->getMorphClass(),
                'reference_id' => $invoice->id,
            ];

            if ($invoice->discount_amount > 0) {
                $entries[] = [
                    'account_id' => $purchases_discount_account_id,
                    'entry_type' => 'credit',
                    'amount' => $invoice->discount_amount,
                    'description' => 'Invoice ' . $invoice->id,
                    'reference_type' => $invoice->getMorphClass(),
                    'reference_id' => $invoice->id,
                ];
            }

        }

        $journal->entries()->createMany($entries);
    }
}