<?php

namespace App\Http\Controllers;

use App\Services\FinancialReportService;
use Illuminate\Http\Request;

class FinancialReportController
{
    public function __construct(
        private readonly FinancialReportService $financialReportService
    ) {}

    public function trialBalance(Request $request)
    {
        $validated = $request->validate([
            'end_date' => 'required|date',
        ]);

        return response()->json(
            $this->financialReportService->generateTrialBalance($validated['end_date'])
        );
    }

    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
        ]);

        return response()->json(
            $this->financialReportService->getBalanceSheet($validated['as_of_date'])
        );
    }

    public function incomeStatement(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return response()->json(
            $this->financialReportService->getIncomeStatement(
                $validated['start_date'],
                $validated['end_date']
            )
        );
    }

    public function generalLedger(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'account_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ]);

        return response()->json(
            $this->financialReportService->getGeneralLedger(
                $validated['start_date'],
                $validated['end_date'],
                $validated['account_id'] ?? null
            )
        );
    }

    public function agedReceivables(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
        ]);

        return response()->json(
            $this->financialReportService->getAgedReceivables($validated['as_of_date'])
        );
    }

    public function agedPayables(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'required|date',
        ]);

        return response()->json(
            $this->financialReportService->getAgedPayables($validated['as_of_date'])
        );
    }

    public function cashFlow(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return response()->json(
            $this->financialReportService->getCashFlow(
                $validated['start_date'],
                $validated['end_date']
            )
        );
    }
}
