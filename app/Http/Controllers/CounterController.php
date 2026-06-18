<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class CounterController extends Controller
{
    public function unInvoicedCompletedOrders()
    {
        $unInvoicedCompletedOrdersCount = Order::query()
            ->where('status_id', 6)
            ->whereDoesntHave('invoices')
            ->count() ?? 0;

        return response()->json($unInvoicedCompletedOrdersCount);
    }
}
