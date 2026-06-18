<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Actions\Invoices\UpdateInvoicePaidAmountAndPaymentStatusAction;
use App\Actions\Parties\UpdateBalanceAction;
use Illuminate\Support\Facades\DB;
use App\Actions\Orders\UpdateOrderPaidAmountAndPaymentStatusAction;

class PaymentController extends Controller
{

    protected $with = ['party', 'invoice', 'bankAccount'];

    protected $searchable = ['payment_number', 'payment_date', 'amount', 'status', 'payment_method', 'payment_type', 'notes'];

    public function index(Request $request)
    {
        $query = Payment::query()
            ->with($this->with)
            ->orderBy('id', 'desc');

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }
        $payments = $query->get();
        return response()->json($payments);
    }

    public function store(StorePaymentRequest $request)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::create($request->validated());

            // update paid amount and payment status
            (new UpdateInvoicePaidAmountAndPaymentStatusAction())->handle($payment->invoice);

            // update order paid amount and payment status
            if($payment->invoice->reference_type === 'order') {
                (new UpdateOrderPaidAmountAndPaymentStatusAction())->handle($payment->invoice->reference);
            }

            // update party balance
            (new UpdateBalanceAction())->handle($payment->party);
            DB::commit();
            return response()->json($payment);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Payment $payment)
    {
        $payment->update($request->all());
        return response()->json($payment);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json($payment);
    }
}
