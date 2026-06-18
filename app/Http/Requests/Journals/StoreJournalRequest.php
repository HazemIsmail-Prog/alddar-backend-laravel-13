<?php

namespace App\Http\Requests\Journals;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('journals_create');
    }

    protected function prepareForValidation(): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($this->input('entries', []) as $entry) {
            if (($entry['entry_type'] ?? '') === 'debit') {
                $totalDebit += (float) ($entry['amount'] ?? 0);
            } else {
                $totalCredit += (float) ($entry['amount'] ?? 0);
            }
        }

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            throw ValidationException::withMessages([
                'entries' => ['Journal entries must be balanced.'],
            ]);
        }

        $this->merge([
            'journal_number' => 'JRN-'.now()->format('YmdHis').random_int(1000, 9999),
            'journal_type' => 'general',
            'status' => 'posted',
            'posted_at' => now(),
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ]);
    }

    public function rules(): array
    {
        return [
            'journal_number' => 'required|string|max:255',
            'journal_type' => 'required|string|max:255',
            'journal_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'total_debit' => 'required|numeric|min:0',
            'total_credit' => 'required|numeric|min:0',
            'status' => 'required|string|max:255',
            'posted_at' => 'nullable|date',

            'entries' => 'required|array|min:2',
            'entries.*.id' => 'nullable|integer',
            'entries.*.account_id' => 'required|exists:chart_of_accounts,id,is_leaf,1',
            'entries.*.entry_type' => 'required|in:debit,credit',
            'entries.*.amount' => 'required|numeric|min:0.001',
            'entries.*.description' => 'nullable|string',
        ];
    }
}
