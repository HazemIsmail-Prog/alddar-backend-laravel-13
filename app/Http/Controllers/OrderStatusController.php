<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController
{
    public function index(Request $request)
    {
        if ($request->has('isList') && $request->boolean('isList')) {
            return response()->json(OrderStatus::select(['id', 'name', 'color'])->get());
        }
    }
}
