<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StockMovementService;
use App\Actions\Stock\UpdateStockLevelAction;
use App\Actions\Invoices\UpdateInvoicePaidAmountAndPaymentStatusAction;
use App\Actions\Parties\UpdateBalanceAction;
use App\Actions\Orders\UpdateOrderPaidAmountAndPaymentStatusAction;
use App\Events\Invoices\InvoiceCreated;

class InvoiceController
{
    protected array $with = ['party', 'items', 'reference'];

    protected array $searchable = ['invoice_number', 'invoice_date', 'status'];

    public function index(Request $request)
    {
        $query = Invoice::query()
            ->with($this->with)
            ->orderBy('id', 'desc');
            if($request->has('invoice_type')) {
                $query->where('invoice_type', $request->invoice_type);
            }
            if($request->has('party_id')) {
                $query->where('party_id', $request->party_id);
            }
            if($request->has('order_id')) {
                $query->where('reference_id', $request->order_id)
                    ->where('reference_type', 'order');
            }
            if($request->has('search')) {
                $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
            }
            if($request->has('status')) {
                $query->where('status', $request->status);
            }
            $invoices = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json($invoices);
    }

    public function show(Invoice $invoice)
    {
        return response()->json($invoice->load($this->with));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $safe = $request->safe();
        $invoiceData = $safe->except('items');
        $itemsData = $safe->input('items', []);

        DB::beginTransaction();
        try {

            // create invoice
            $invoice = Invoice::create($invoiceData);

            // paid amount and payment status already updated in the StoreInvoiceRequest

            // update order paid amount and payment status
            if($invoice->reference_type === 'order') {
                (new UpdateOrderPaidAmountAndPaymentStatusAction())->handle($invoice->reference);
            }

            // update party balance
            (new UpdateBalanceAction())->handle($invoice->party);

            // create invoice items
            $invoice->syncMany('items', $itemsData);


            // get the invoice items
            $invoiceItems = $invoice->items->toArray();

            // create stock movements for the invoice items where has warehouse id and product id
            $movement_type = match ($invoice->invoice_type) {
                'purchase' => 'in',
                'sales' => 'out',
                'credit_note' => 'out',
                'debit_note' => 'in',
                default => throw new \Exception('Invalid invoice type'),
            };

            $transaction_type = match ($invoice->invoice_type) {
                'purchase' => 'purchase',
                'sales' => 'sale',
                'credit_note' => 'return',
                'debit_note' => 'return',
                default => throw new \Exception('Invalid invoice type'),
            };

            $stockMovementsData = StockMovementService::getInvoiceStockMovementsData($invoiceItems, $movement_type, $transaction_type);

            if (!empty($stockMovementsData)) {

                $invoice->stockMovements()->createMany($stockMovementsData);
                (new UpdateStockLevelAction())->handle();

            }

            // create journal for the invoice
            // create journal entries for the invoice
            DB::commit();
            event(new InvoiceCreated($invoice));
            return response()->json($invoice->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {

        $safe = $request->safe();
        $invoiceData = $safe->except('items');
        $itemsData = $safe->input('items', []);

        // return response()->json($invoiceData);

        DB::beginTransaction();
        try {

            // get the old total amount
            $oldTotalAmount = $invoice->total_amount;

            $invoice->update($invoiceData);

            // update paid amount and balance
            (new UpdateInvoicePaidAmountAndPaymentStatusAction())->handle($invoice);

            // update order paid amount and payment status
            if($invoice->reference_type === 'order') {
                (new UpdateOrderPaidAmountAndPaymentStatusAction())->handle($invoice->reference);
            }

            // update party balance
            (new UpdateBalanceAction())->handle($invoice->party);

            // update invoice items
            $invoice->syncMany('items', $itemsData);

            // get the invoice items
            $invoiceItems = $invoice->items->toArray();


            // create stock movements for the invoice items where has warehouse id and product id
            $movement_type = match ($invoice->invoice_type) {
                'purchase' => 'in',
                'sales' => 'out',
                'credit_note' => 'out',
                'debit_note' => 'in',
                default => throw new \Exception('Invalid invoice type'),
            };

            $transaction_type = match ($invoice->invoice_type) {
                'purchase' => 'purchase',
                'sales' => 'sale',
                'credit_note' => 'return',
                'debit_note' => 'return',
                default => throw new \Exception('Invalid invoice type'),
            };

            $invoice->stockMovements()->delete();

            $stockMovementsData = StockMovementService::getInvoiceStockMovementsData($invoiceItems, $movement_type, $transaction_type);

            if (!empty($stockMovementsData)) {

                $invoice->stockMovements()->createMany($stockMovementsData);
                (new UpdateStockLevelAction())->handle();

            }

            DB::commit();
            return response()->json($invoice->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function destroy(Invoice $invoice)
    {
        // $action->handle($invoice);

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    

}
