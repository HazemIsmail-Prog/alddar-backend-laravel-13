<?php

namespace App\Http\Requests\Invoices;

use App\Enums\InvoiceType;
use App\Enums\InvoiceStatus;
use App\Models\StockLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {

        $invoiceNumber = match (InvoiceType::from($this->invoice_type)) {
            InvoiceType::SALES => 'INV-' . now()->format('YmdHis').random_int(1000, 9999),
            InvoiceType::PURCHASE => 'BILL-' . now()->format('YmdHis').random_int(1000, 9999),
            InvoiceType::CREDIT_NOTE => 'CN-' . now()->format('YmdHis').random_int(1000, 9999),
            InvoiceType::DEBIT_NOTE => 'DN-' . now()->format('YmdHis').random_int(1000, 9999),
            default => null,
        };

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
                        $validationErrors['items.'.$index.'.quantity'] = [
                            'ar' => 'الكمية غير متوفرة في المخزن المختار', 
                            'en' => 'Quantity not available in selected warehouse'
                        ];
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
                    'account_id' => $item['account_id'],
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
        $invoice_status = InvoiceStatus::DRAFT->value;

        $this->merge([
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
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
            'reference_id' => 'required|numeric',
            'reference_type' => 'required|string',
            'invoice_type' => ['required', 'string', 'max:255', Rule::in(InvoiceType::values())],
            'party_id' => 'required|exists:parties,id',
            'invoice_date' => 'required|date',
            'status' => ['required', 'string', 'max:255', Rule::in(InvoiceStatus::values())],
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0.001',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',


            'items' => 'required|array|min:1',
            // 'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.product_id' => 'nullable|required_if:items.*.description,null|exists:products,id',
            'items.*.warehouse_id' => 'nullable|required_unless:items.*.product_id,null|prohibited_if:items.*.product_id,null|exists:warehouses,id',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id,is_leaf,1',
            'items.*.description' => 'nullable|required_if:items.*.product_id,null|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0.001',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        
        foreach ($validator->errors()->toArray() as $field => $messages) {
            $errors[$field] = [];
            
            foreach ($messages as $message) {
                $errors[$field] = $this->translatedMessage($message);
            }
        }

        throw new HttpResponseException(
            response()->json([
                // 'success' => false,
                'errors' => (object) $errors
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    private function translatedMessage(string $message) : object
    {

        switch ($message) {
            case 'total_amount.min':
                return (object) [
                    'ar' => 'اجمالي الفاتورة يجب أن يكون أكبر من 0', 
                    'en' => 'The total amount must be greater than 0'
                ];
            case 'product_id.required_if':
                return (object) [
                    'ar' => 'اختر منتج أو أدخل وصف للبند', 
                    'en' => 'The product or description for the item is required'
                ];
            case 'warehouse_id.required_unless':
                return (object) [
                    'ar' => 'يجب اختيار مخزن عند اختيار منتج', 
                    'en' => 'The warehouse must be present'
                ];
            case 'warehouse_id.prohibited_if':
                return (object) [
                    'ar' => 'غير مسموح باختيار مخزن في عدم وجود منتج', 
                    'en' => 'The warehouse is not allowed when the product is not present'
                ];
            case 'quantity.min':
                return (object) [
                    'ar' => 'يجب ادخال كمية أكبر من 0', 
                    'en' => 'The quantity must be greater than 0'
                ];
            case 'quantity.required':
                return (object) [
                    'ar' => 'يجب ادخال الكمية', 
                    'en' => 'The quantity is required'
                ];
            case 'unit_price.min':
                return (object) [
                    'ar' => 'يجب أن يكون السعر أكبر من 0', 
                    'en' => 'The unit price must be greater than 0'
                ];
            case 'unit_price.required':
                return (object) [
                    'ar' => 'يجب ادخال سعر الوحدة', 
                    'en' => 'The unit price is required'
                ];
            case 'discount_amount.min':
                return (object) [
                    'ar' => 'يجب أن يكون الخصم أكبر من 0', 
                    'en' => 'The discount must be greater than 0'
                ];
            case 'discount_amount.required':
                return (object) [
                    'ar' => 'يجب ادخال قيمة الخصم', 
                    'en' => 'The discount value is required'
                ];
            case 'total_price.min':
                return (object) [
                    'ar' => 'اجمالي السطر يجب ان يكون اكبر من 0', 
                    'en' => 'The total price must be greater than 0'
                ];
            case 'account_id.required':
                return (object) [
                    'ar' => 'يجب اختيار حساب للبند', 
                    'en' => 'The account for the item is required'
                ];
            case 'description.required_if':
                return (object) [
                    'ar' => 'اختر منتج أو أدخل وصف للبند', 
                    'en' => 'Choose a product or enter a description'
                ];
            default:
                return (object) [
                    'ar' => $message, 
                    'en' => $message
                ];
        }

    }

    public function messages(): array
    {
        return [
            'total_amount.min' => 'total_amount.min',
            'discount_amount.required' => 'discount_amount.required',
            'items.*.product_id.required_if' => 'product_id.required_if',
            'items.*.warehouse_id.required_unless' => 'warehouse_id.required_unless',
            'items.*.warehouse_id.prohibited_if' => 'warehouse_id.prohibited_if',
            'items.*.quantity.required' => 'quantity.required',
            'items.*.quantity.min' => 'quantity.min',
            'items.*.unit_price.required' => 'unit_price.required',
            'items.*.unit_price.min' => 'unit_price.min',
            'items.*.discount_amount.min' => 'discount_amount.min',
            'items.*.total_price.min' => 'total_price.min',
            'items.*.account_id.required' => 'account_id.required',
            'items.*.description.required_if' => 'description.required_if',
            'items.*.discount_amount.required' => 'discount_amount.required',
        ];
    }

}
