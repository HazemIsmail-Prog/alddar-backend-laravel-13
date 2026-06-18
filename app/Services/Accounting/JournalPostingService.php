<?php

namespace App\Services\Accounting;

use App\Data\Invoices\CalculatedInvoiceData;
use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\Journal;
use RuntimeException;

final class JournalPostingService
{
    public function postForInvoice(Invoice $invoice, CalculatedInvoiceData $calculated): Journal
    {
        $postingConfig = $this->resolvePostingConfig($invoice->invoice_type);
        $discountAmount = (float) $invoice->discount_amount;
        $grossAmount = $calculated->subtotal;
        $netAmount = $calculated->totalAmount;

        $accountCodes = [$postingConfig['debit_account_code'], $postingConfig['credit_account_code']];
        if ($discountAmount > 0 && ! empty($postingConfig['discount_account_code'])) {
            $accountCodes[] = $postingConfig['discount_account_code'];
        }

        $accounts = ChartOfAccount::query()->whereIn('account_code', $accountCodes)->get()->keyBy('account_code');

        if (! $accounts->has($postingConfig['debit_account_code']) || ! $accounts->has($postingConfig['credit_account_code'])) {
            throw new RuntimeException('Required chart of accounts are missing for journal posting.');
        }

        if ($discountAmount > 0 && ! empty($postingConfig['discount_account_code']) && ! $accounts->has($postingConfig['discount_account_code'])) {
            throw new RuntimeException('Discount account is missing for journal posting.');
        }

        $entries = [
            [
                'account_id' => $accounts[$postingConfig['debit_account_code']]->id,
                'entry_type' => 'debit',
                'amount' => $netAmount,
                'reference_number' => $invoice->invoice_number,
                'description' => $postingConfig['description'],
                'reference_type' => $invoice->getMorphClass(),
                'reference_id' => $invoice->id,
            ],
            [
                'account_id' => $accounts[$postingConfig['credit_account_code']]->id,
                'entry_type' => 'credit',
                'amount' => $grossAmount,
                'reference_number' => $invoice->invoice_number,
                'description' => $postingConfig['description'],
                'reference_type' => $invoice->getMorphClass(),
                'reference_id' => $invoice->id,
            ],
        ];

        if ($discountAmount > 0 && ! empty($postingConfig['discount_account_code'])) {
            $entries[] = [
                'account_id' => $accounts[$postingConfig['discount_account_code']]->id,
                'entry_type' => $postingConfig['discount_entry_type'],
                'amount' => $discountAmount,
                'reference_number' => $invoice->invoice_number,
                'description' => 'Invoice discount',
                'reference_type' => $invoice->getMorphClass(),
                'reference_id' => $invoice->id,
            ];
        }

        $totalDebit = collect($entries)->where('entry_type', 'debit')->sum('amount');
        $totalCredit = collect($entries)->where('entry_type', 'credit')->sum('amount');

        $journal = $invoice->journals()->create([
            'journal_number' => 'JRN-'.now()->format('YmdHis').random_int(1000, 9999),
            'journal_type' => $postingConfig['journal_type'],
            'journal_date' => $invoice->invoice_date,
            'reference_number' => $invoice->invoice_number,
            'description' => $postingConfig['description'],
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'posted',
            'posted_at' => now(),
        ]);

        $journal->entries()->createMany($entries);

        return $journal;
    }

    private function resolvePostingConfig(string $invoiceType): array
    {
        return match ($invoiceType) {
            'sales' => [
                'journal_type' => 'sales',
                'debit_account_code' => '1200',
                'credit_account_code' => '4100',
                'discount_account_code' => '4300',
                'discount_entry_type' => 'debit',
                'description' => 'Sales invoice posted',
            ],
            'purchase' => [
                'journal_type' => 'purchase',
                'debit_account_code' => '9120',
                'credit_account_code' => '2110',
                'discount_account_code' => null,
                'discount_entry_type' => null,
                'description' => 'Purchase invoice posted',
            ],
            'credit_note' => [
                'journal_type' => 'credit_note',
                'debit_account_code' => '4200',
                'credit_account_code' => '1200',
                'discount_account_code' => null,
                'discount_entry_type' => null,
                'description' => 'Credit note posted',
            ],
            'debit_note' => [
                'journal_type' => 'debit_note',
                'debit_account_code' => '1200',
                'credit_account_code' => '4100',
                'discount_account_code' => '4300',
                'discount_entry_type' => 'debit',
                'description' => 'Debit note posted',
            ],
            default => throw new RuntimeException('Unsupported invoice type for journal posting.'),
        };
    }
}
