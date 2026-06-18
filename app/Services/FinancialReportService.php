<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Support\Carbon;

class FinancialReportService
{
    /**
     * Generate Trial Balance - only include leaf accounts
     */
    public function generateTrialBalance($endDate)
    {
        // Only get leaf accounts (where actual transactions are posted)
        $leafAccounts = ChartOfAccount::where('is_leaf', true)
            ->where('is_active', true)
            ->get();

        $trialBalance = [];

        foreach ($leafAccounts as $account) {
            $balance = JournalEntry::where('account_id', $account->id)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw('SUM(CASE WHEN entry_type = "debit" THEN amount ELSE 0 END) as total_debit')
                ->selectRaw('SUM(CASE WHEN entry_type = "credit" THEN amount ELSE 0 END) as total_credit')
                ->first();

            $debit = $balance->total_debit ?? 0;
            $credit = $balance->total_credit ?? 0;
            $netBalance = $debit - $credit;

            $trialBalance[] = [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'debit' => $netBalance > 0 && $account->normal_balance === 'debit' ? $netBalance : 0,
                'credit' => $netBalance < 0 && $account->normal_balance === 'credit' ? abs($netBalance) : 0,
            ];
        }

        return $trialBalance;
    }

    /**
     * Get Balance Sheet with proper grouping
     */
    public function getBalanceSheet($asOfDate)
    {
        // Get all accounts (including parent groups)
        $allAccounts = ChartOfAccount::with('children')
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Calculate balances only for leaf accounts
        $leafBalances = [];
        $leafAccounts = ChartOfAccount::where('is_leaf', true)->get();

        foreach ($leafAccounts as $account) {
            $balance = $this->calculateAccountBalance($account->id, $asOfDate);
            $leafBalances[$account->id] = $balance;
        }

        // Roll up balances to parent accounts
        $balanceSheet = [];
        $rootAccounts = $allAccounts->where('parent_id', null);

        foreach ($rootAccounts as $rootAccount) {
            $balanceSheet[] = $this->buildAccountHierarchy(
                $rootAccount,
                $allAccounts,
                $leafBalances
            );
        }

        return $balanceSheet;
    }

    /**
     * Recursively build account hierarchy with rolled-up balances
     */
    private function buildAccountHierarchy($account, $allAccounts, $leafBalances)
    {
        $result = [
            'account_code' => $account->account_code,
            'account_name' => $account->account_name,
            'is_leaf' => $account->is_leaf,
            'balance' => 0,
            'children' => [],
        ];

        if ($account->is_leaf) {
            // Leaf account - use direct balance
            $result['balance'] = $leafBalances[$account->id] ?? 0;
        } else {
            // Parent account - sum children balances
            $children = $allAccounts->where('parent_id', $account->id);
            $totalBalance = 0;

            foreach ($children as $child) {
                $childResult = $this->buildAccountHierarchy($child, $allAccounts, $leafBalances);
                $result['children'][] = $childResult;
                $totalBalance += $childResult['balance'];
            }

            $result['balance'] = $totalBalance;
        }

        return $result;
    }

    private function calculateAccountBalance($accountId, $asOfDate)
    {
        $account = ChartOfAccount::find($accountId);

        $balance = JournalEntry::where('account_id', $accountId)
            ->whereDate('created_at', '<=', $asOfDate)
            ->selectRaw('SUM(CASE WHEN entry_type = "debit" THEN amount ELSE 0 END) as total_debit')
            ->selectRaw('SUM(CASE WHEN entry_type = "credit" THEN amount ELSE 0 END) as total_credit')
            ->first();

        $debit = $balance->total_debit ?? 0;
        $credit = $balance->total_credit ?? 0;

        // Return balance based on normal balance type
        if ($account->normal_balance === 'debit') {
            return $debit - $credit;
        } else {
            return $credit - $debit;
        }
    }

    public function getIncomeStatement($startDate, $endDate)
    {
        $leafAccounts = ChartOfAccount::query()
            ->where('is_leaf', true)
            ->where('is_active', true)
            ->whereIn('account_type', ['income', 'expense', 'cost_of_goods_sold'])
            ->orderBy('account_code')
            ->get();

        $revenue = [];
        $costOfGoodsSold = [];
        $expenses = [];

        foreach ($leafAccounts as $account) {
            $totals = JournalEntry::query()
                ->where('account_id', $account->id)
                ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                ->selectRaw('SUM(CASE WHEN entry_type = "debit" THEN amount ELSE 0 END) as total_debit')
                ->selectRaw('SUM(CASE WHEN entry_type = "credit" THEN amount ELSE 0 END) as total_credit')
                ->first();

            $debit = (float) ($totals->total_debit ?? 0);
            $credit = (float) ($totals->total_credit ?? 0);

            $amount = $account->normal_balance === 'debit'
                ? max(0, $debit - $credit)
                : max(0, $credit - $debit);

            if ($amount <= 0) {
                continue;
            }

            $row = [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'amount' => $amount,
            ];

            if ($account->account_type === 'income') {
                $revenue[] = $row;
            } elseif ($account->account_type === 'cost_of_goods_sold') {
                $costOfGoodsSold[] = $row;
            } else {
                $expenses[] = $row;
            }
        }

        $revenueTotal = array_sum(array_column($revenue, 'amount'));
        $cogsTotal = array_sum(array_column($costOfGoodsSold, 'amount'));
        $expenseTotal = array_sum(array_column($expenses, 'amount'));
        $grossProfit = $revenueTotal - $cogsTotal;
        $netIncome = $grossProfit - $expenseTotal;

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'revenue' => $revenue,
            'cost_of_goods_sold' => $costOfGoodsSold,
            'expenses' => $expenses,
            'totals' => [
                'revenue' => $revenueTotal,
                'cost_of_goods_sold' => $cogsTotal,
                'gross_profit' => $grossProfit,
                'expenses' => $expenseTotal,
                'net_income' => $netIncome,
            ],
        ];
    }

    public function getGeneralLedger($startDate, $endDate, $accountId = null)
    {
        $query = JournalEntry::query()
            ->with([
                'account:id,account_code,account_name',
                'journal:id,journal_number,journal_type,journal_date',
            ])
            ->whereHas('journal', function ($journalQuery) use ($startDate, $endDate) {
                $journalQuery->whereBetween('journal_date', [$startDate, $endDate]);
            })
            ->orderBy('created_at');

        if (! empty($accountId)) {
            $query->where('account_id', $accountId);
        }

        $entries = $query->get();

        return $entries->map(function (JournalEntry $entry) {
            return [
                'journal_number' => $entry->journal?->journal_number,
                'journal_type' => $entry->journal?->journal_type,
                'journal_date' => $entry->journal?->journal_date,
                'account_id' => $entry->account_id,
                'account_code' => $entry->account?->account_code,
                'account_name' => $entry->account?->account_name,
                'entry_type' => $entry->entry_type,
                'amount' => (float) $entry->amount,
                'reference_number' => $entry->reference_number,
                'description' => $entry->description,
                'created_at' => $entry->created_at?->toDateTimeString(),
            ];
        })->values();
    }

    public function getAgedReceivables($asOfDate)
    {
        return $this->getAgedInvoicesByType($asOfDate, 'sales');
    }

    public function getAgedPayables($asOfDate)
    {
        return $this->getAgedInvoicesByType($asOfDate, 'purchase');
    }

    public function getCashFlow($startDate, $endDate)
    {
        $cashAccounts = ChartOfAccount::query()
            ->whereIn('account_type', ['cash', 'bank'])
            ->where('is_active', true)
            ->pluck('id');

        $entries = JournalEntry::query()
            ->whereIn('account_id', $cashAccounts)
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->with('account:id,account_code,account_name')
            ->get();

        $byAccount = [];
        $totalInflow = 0;
        $totalOutflow = 0;

        foreach ($entries as $entry) {
            $key = (string) $entry->account_id;
            if (! isset($byAccount[$key])) {
                $byAccount[$key] = [
                    'account_id' => $entry->account_id,
                    'account_code' => $entry->account?->account_code,
                    'account_name' => $entry->account?->account_name,
                    'inflow' => 0,
                    'outflow' => 0,
                    'net' => 0,
                ];
            }

            $amount = (float) $entry->amount;
            if ($entry->entry_type === 'debit') {
                $byAccount[$key]['inflow'] += $amount;
                $totalInflow += $amount;
            } else {
                $byAccount[$key]['outflow'] += $amount;
                $totalOutflow += $amount;
            }
            $byAccount[$key]['net'] = $byAccount[$key]['inflow'] - $byAccount[$key]['outflow'];
        }

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'totals' => [
                'inflow' => $totalInflow,
                'outflow' => $totalOutflow,
                'net_cash_flow' => $totalInflow - $totalOutflow,
            ],
            'by_account' => array_values($byAccount),
        ];
    }

    private function getAgedInvoicesByType($asOfDate, $invoiceType)
    {
        $asOf = Carbon::parse($asOfDate)->startOfDay();

        $invoices = Invoice::query()
            ->with('party:id,name')
            ->where('invoice_type', $invoiceType)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get();

        $rows = [];
        $bucketTotals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_90_plus' => 0,
        ];

        foreach ($invoices as $invoice) {
            $totalAmount = (float) $invoice->total_amount;
            $amountPaid = (float) $invoice->amount_paid / 1000;
            $outstanding = $totalAmount - $amountPaid;

            if ($outstanding <= 0) {
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $daysOverdue = $dueDate->gt($asOf) ? 0 : $dueDate->diffInDays($asOf);

            $bucket = 'current';
            if ($daysOverdue >= 1 && $daysOverdue <= 30) {
                $bucket = 'days_1_30';
            } elseif ($daysOverdue <= 60) {
                $bucket = 'days_31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = 'days_61_90';
            } elseif ($daysOverdue > 90) {
                $bucket = 'days_90_plus';
            }

            $bucketTotals[$bucket] += $outstanding;

            $rows[] = [
                'invoice_number' => $invoice->invoice_number,
                'party_name' => $invoice->party?->name,
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'days_overdue' => $daysOverdue,
                'outstanding' => $outstanding,
                'bucket' => $bucket,
            ];
        }

        return [
            'as_of_date' => $asOfDate,
            'rows' => $rows,
            'totals' => $bucketTotals,
            'total_outstanding' => array_sum($bucketTotals),
        ];
    }
}
