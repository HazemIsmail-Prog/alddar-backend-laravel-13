<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\Invoices\InvoiceCreated;
use App\Actions\UpdateStockLevelsAction;
use App\Actions\UpdatePartyBalanceAction;
use App\Actions\UpdateLeafAccountsBalanceAction;
use App\Actions\CreateInvoiceStockMovmentsAction;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Actions\UpdateOrCreateInvoiceJournalAction;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Actions\UpdateOrderPaidAmountAndPaymentStatusAction;
use App\Actions\UpdateInvoicePaidAmountAndPaymentStatusAction;

class InvoiceController
{

    public function __construct(
        protected UpdateStockLevelsAction $updateStockLevelsAction,
        protected UpdatePartyBalanceAction $updatePartyBalanceAction,
        protected UpdateLeafAccountsBalanceAction $updateLeafAccountsBalanceAction,
        protected CreateInvoiceStockMovmentsAction $createInvoiceStockMovmentsAction,
        protected UpdateOrCreateInvoiceJournalAction $updateOrCreateInvoiceJournalAction,
        protected UpdateOrderPaidAmountAndPaymentStatusAction $updateOrderPaidAmountAndPaymentStatusAction,
        protected UpdateInvoicePaidAmountAndPaymentStatusAction $updateInvoicePaidAmountAndPaymentStatusAction,
    ) {}

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
        
        if(!request()->user()->hasPermission('invoices_create')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لإنشاء الفاتورة', 
                    'en' => 'You do not have permission to create an invoice'
                ],
            ], 403);
        }

        $safe = $request->safe();
        $invoiceData = $safe->except('items');
        $itemsData = $safe->input('items', []);

        
        DB::beginTransaction();
        
        try {
            
            $invoice = Invoice::create($invoiceData);

            if($invoice->reference_type === 'order') {
                $order = $invoice->reference;
                $this->updateOrderPaidAmountAndPaymentStatusAction->handle($order);
            }
            
            $this->updatePartyBalanceAction->handle($invoice->party);
            
            $invoice->syncMany('items', $itemsData);

            $this->createInvoiceStockMovmentsAction->handle($invoice);

            $this->updateStockLevelsAction->handle();

            $this->updateOrCreateInvoiceJournalAction->handle($invoice);

            $this->updateLeafAccountsBalanceAction->handle();

            DB::commit();

            event(new InvoiceCreated($invoice));

            return response()->json($invoice->load($this->with));

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);

        }

    }

    public function update(Invoice $invoice, UpdateInvoiceRequest $request)
    {

        if(!request()->user()->hasPermission('invoices_update')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لتحديث الفاتورة', 
                    'en' => 'You do not have permission to update the invoice'
                ],
            ], 403);
        }

        $safe = $request->safe();
        $invoiceData = $safe->except('items');
        $itemsData = $safe->input('items', []);

        DB::beginTransaction();

        try {

            $invoice->update($invoiceData);

            $this->updateInvoicePaidAmountAndPaymentStatusAction->handle($invoice);

            if($invoice->reference_type === 'order') {
                $order = $invoice->reference;
                $this->updateOrderPaidAmountAndPaymentStatusAction->handle($order);
            }

            $this->updatePartyBalanceAction->handle($invoice->party);

            $invoice->syncMany('items', $itemsData);

            $this->createInvoiceStockMovmentsAction->handle($invoice);

            $this->updateStockLevelsAction->handle();

            $this->updateOrCreateInvoiceJournalAction->handle($invoice);

            $this->updateLeafAccountsBalanceAction->handle();

            DB::commit();

            return response()->json($invoice->load($this->with));

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);

        }

    }

    public function destroy(Invoice $invoice)
    {
        
        if(!request()->user()->hasPermission('invoices_delete')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لحذف الفاتورة', 
                    'en' => 'You do not have permission to delete the invoice'
                ],
            ], 403);
        }

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    

}
