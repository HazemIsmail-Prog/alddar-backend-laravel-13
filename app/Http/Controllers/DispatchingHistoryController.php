<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DispatchingHistoryController extends Controller
{
    public function index(Order $order)
    {
        $dispatchingHistories = $order->dispatchingHistories()->with('creator', 'status', 'technician')->get();
        return response()->json($dispatchingHistories);
    }
}
