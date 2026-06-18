<?php

namespace App\Http\Requests\ChartOfAccounts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ChartOfAccount;

class StoreChartOfAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // prepare the request data
    public function prepareForValidation()
    {
        $parent = null;
        $level = 0;
        if (! empty($this->input('parent_id'))) {
            $parent = ChartOfAccount::query()->find($this->input('parent_id'));
            $level = ($parent?->level ?? 0) + 1;
        }
        $this->merge([
            'is_leaf' => $parent ? false : true,
            'level' => $level,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_code' => 'required|string|max:50|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => ['required', Rule::in([
                'asset',
                'liability',
                'equity',
                'income',
                'expense',
                'cost_of_goods_sold',
                'bank',
                'cash',
                'accounts_receivable',
                'accounts_payable',
                'inventory',
            ])],
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'level' => 'required|integer|min:0',
            'is_leaf' => 'required|boolean',
            'is_system_account' => 'required|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ];
    }
}
