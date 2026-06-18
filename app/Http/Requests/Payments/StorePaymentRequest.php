<?php

namespace App\Http\Requests\Payments;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Invoice;
use Illuminate\Validation\ValidationException;
class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $invoice = Invoice::find($this->invoice_id);
        if (!$invoice) {
            throw ValidationException::withMessages(['invoice_id' => ['Invoice not found']]);
        }

        $invoice_remaining_amount = $invoice->total_amount - $invoice->amount_paid;

        if ($this->amount > $invoice_remaining_amount) {
            throw ValidationException::withMessages(['amount' => ['Amount is greater than the remaining amount']]);
        }

        if ($this->amount < 0) {
            throw ValidationException::withMessages(['amount' => ['Amount is less than 0']]);
        }

        $prefix = match ($invoice->invoice_type) {
            'sales' => 'REC-',
            'purchase' => 'PAY-',
            'credit_note' => 'REC-',
            'debit_note' => 'PAY-',
            default => null,
        };

        $payment_number = $prefix . now()->format('YmdHis').random_int(1000, 9999);

        $this->merge([
            'payment_number' => $payment_number,
            'party_id' => $invoice->party_id,
            'payment_type' => $invoice->invoice_type === 'sales' ? 'receipt' : 'payment',
            'payment_date' => now(),
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
            'payment_number' => 'required|string|max:255',
            'invoice_id' => 'required|exists:invoices,id',
            'party_id' => 'required|exists:parties,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,credit_card,debit_card,online',
            'payment_type' => 'required|string|in:receipt,payment',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
            'status' => 'required|string|in:pending,completed,failed,cancelled',
        ];
    }
}
