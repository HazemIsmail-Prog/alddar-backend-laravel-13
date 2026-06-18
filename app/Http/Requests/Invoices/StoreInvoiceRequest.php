<?php

namespace App\Http\Requests\Invoices;

use App\Models\StockLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $invoiceNumber = match ($this->invoice_type) {
            'sales' => 'INV-' . now()->format('YmdHis').random_int(1000, 9999),
            'purchase' => 'BILL-' . now()->format('YmdHis').random_int(1000, 9999),
            'credit_note' => 'CN-' . now()->format('YmdHis').random_int(1000, 9999),
            'debit_note' => 'DN-' . now()->format('YmdHis').random_int(1000, 9999),
            default => null,
        };

        $invoiceDate = now()->format('Y-m-d');

        $itemsData = [];
        $validationErrors = [];
        if ($this->input('items')) {

            $sum_quantity_by_product_and_warehouse = [];
            foreach ($this->input('items', []) as $item) {
                $sum_quantity_by_product_and_warehouse[$item['product_id'].':'.$item['warehouse_id']] = ($sum_quantity_by_product_and_warehouse[$item['product_id'].':'.$item['warehouse_id']] ?? 0) + $item['quantity'];
            }

            foreach ($this->input('items', []) as $index => $item) {
                if ($item['product_id'] && $item['warehouse_id'] && $this->input('invoice_type') === 'sales') {
                    $available_quantity = StockLevel::where('product_id', $item['product_id'])->where('warehouse_id', $item['warehouse_id'])->first()->quantity ?? 0;
                    if ($available_quantity < $sum_quantity_by_product_and_warehouse[$item['product_id'].':'.$item['warehouse_id']]) {
                        $validationErrors['items.'.$index.'.quantity'] = ['Quantity not available in selected warehouse'];
                    }
                }
                $itemsData[] = [
                    // 'id' => $item['id'] ?? null,
                    'description' => $item['description'],
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'total_price' => $item['unit_price'] * $item['quantity'] - $item['discount_amount'],
                ];
            }
        }

        if (count($validationErrors) > 0) {
            throw ValidationException::withMessages($validationErrors);
        }

        $invoice_subtotal = array_sum(array_column($itemsData, 'total_price'));
        $invoice_discount_amount = $this->input('discount_amount');
        $invoice_total_amount = $invoice_subtotal - $invoice_discount_amount;
        $invoice_amount_paid = 0;
        $invoice_status = 'draft';

        $this->merge([
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'items' => $itemsData,
            'subtotal' => $invoice_subtotal,
            'discount_amount' => $invoice_discount_amount,
            'total_amount' => $invoice_total_amount,
            'amount_paid' => $invoice_amount_paid,
            'status' => $invoice_status,
        ]);
    }

    public function rules(): array
    {
        return [
            'invoice_number' => 'required|string|max:64',
            'reference_id' => 'nullable|numeric',
            'reference_type' => 'nullable|string',
            'invoice_type' => 'required|string|max:255|in:sales,purchase,credit_note,debit_note',
            'party_id' => 'required|exists:parties,id',
            'invoice_date' => 'required|date',
            'status' => 'required|string|max:255|in:draft,sent,approved,partially_paid,paid,overdue,cancelled',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0.001',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',


            'items' => 'required|array|min:1',
            // 'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.product_id' => 'nullable|required_if:items.*.description,null|exists:products,id',
            'items.*.warehouse_id' => 'nullable|required_unless:items.*.product_id,null|prohibited_if:items.*.product_id,null|exists:warehouses,id',
            'items.*.description' => 'nullable|required_if:items.*.product_id,null|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0.001',
        ];
    }
}
