<?php

namespace App\Http\Controllers;

use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderUpdated;
use App\Events\Orders\OrderDeleted;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use Illuminate\Support\Facades\DB;

class OrderController
{
    protected array $with = [
        'items',
        'party',
        'department',
        'technician',
        'status',
        'location',
        'phone',
    ];

    protected array $searchable = ['order_date','order_number'];

    public function index(Request $request)
    {
        if(!request()->user()->hasPermission('sales_orders_view') && $request->boolean('order_type') === 'sales') {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض الطلبات المبيعات', 
                    'en' => 'You do not have permission to view sales orders'
                ],
            ], 403);
        }

        if(!request()->user()->hasPermission('purchase_orders_view') && $request->boolean('order_type') === 'purchase') {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض الطلبات الشراء', 
                    'en' => 'You do not have permission to view purchase orders'
                ],
            ], 403);
        }



        $query = Order::query()
            ->with($this->with)
            ->orderBy('id', 'desc');
            if($request->has('order_type')) {
                $query->where('order_type', $request->order_type);
            }
            if($request->has('party_id')) {
                $query->where('party_id', $request->party_id);
            }
            if($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }
            if($request->has('completed_at_from')) {
                $query->whereDate('completed_at', '>=', $request->completed_at_from);
            }
            if($request->has('completed_at_to')) {
                $query->whereDate('completed_at', '<=', $request->completed_at_to);
            }
            if($request->has('status_id')) {
                $query->where('status_id', $request->status_id);
            }
            if($request->has('cancelled_at_from')) {
                $query->whereDate('cancelled_at', '>=', $request->cancelled_at_from);
            }
            if($request->has('cancelled_at_to')) {
                $query->whereDate('cancelled_at', '<=', $request->cancelled_at_to);
            }
            if($request->has('technician_id')) {
                $query->where('technician_id', $request->technician_id);
            }
            if($request->has('search')) {
                $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
            }
            if($request->has('status')) {
                $query->where('status', $request->status);
            }

        $orders = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json($orders);

    }

    public function show(Order $order)
    {
        return response()->json($order->load($this->with));
    }

    public function store(StoreOrderRequest $request)
    {

        $safe = $request->safe();
        $orderData = $safe->except('items');
        $itemsData = $safe->input('items', []);

        DB::beginTransaction();
        try {
            $order = Order::create($orderData);
            if (!empty($itemsData)) {
                $order->syncMany('items', $itemsData);
            }
            $order->dispatchingHistories()->create();
            DB::commit();
            broadcast(new OrderCreated($order->load($this->with)));
            return response()->json($order->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $safe = $request->safe();
        $orderData = $safe->except('items');
        $itemsData = $safe->input('items', []);

        DB::beginTransaction();
        try {
            $order->update($orderData);
            $order->syncMany('items', $itemsData);
            DB::commit();
            broadcast(new OrderUpdated($order->load($this->with)));
            return response()->json($order->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Order $order)
    {
        DB::beginTransaction();
        try {
            $order->items()->delete();
            $order->delete();
            DB::commit();
            broadcast(new OrderDeleted($order->load($this->with)));
            return response()->json($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
