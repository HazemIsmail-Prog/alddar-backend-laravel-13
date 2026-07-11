<?php

namespace App\Http\Controllers;

use App\Events\Orders\OrderReceived;
use App\Events\Orders\OrderReached;
use App\Events\Orders\OrderCompleted;
use App\Models\Order;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TechnicianController
{

    private function loadRelatedData(Model $model)
    {
        return $model
            ->load(['party:id,name', 'location', 'phone'])
            ->loadCount('invoices')
            ->loadCount('attachments')
            ->loadCount(['comments as unread_comments_count' => function ($query) {
                $query->whereNotIn('id', request()->user()->commentReaders()->pluck('comments.id'));
            }]);
    }

    public function index(Department $department)
    {
        $inProgressOrder = Order::query()
            ->where('department_id', $department->id)
            ->where('technician_id', request()->user()->id)
            ->where('is_inprogress', true)
            ->first();

        if ($inProgressOrder) {
            return response()->json($this->loadRelatedData($inProgressOrder));
        }

        // return minimum sort number order
        $minimumSortNumberOrder = Order::query()
            ->where('department_id', $department->id)
            ->where('technician_id', request()->user()->id)
            ->whereIn('status_id', [3, 4, 5])
            ->orderBy('sort_number', 'asc')
            ->first();

        if ($minimumSortNumberOrder) {
            return response()->json($this->loadRelatedData($minimumSortNumberOrder));
        }

        return response()->noContent();
    }

    public function setOrderReceived(Department $department, Order $order)
    {

        if ($this->ordersNotMatched($department, $order) && $order->status_id !== 3) {
            return response()->json(['message' => 'Order Not matched, Refreshing the page may help.'], 400);
        }

        DB::beginTransaction();

        try {
            
            $order->update([
                'status_id' => 4,
                'is_inprogress' => true,
            ]);
            
            $order->dispatchingHistories()->create();

            DB::commit();

            broadcast(new OrderReceived($order));

            return response()->json($this->loadRelatedData($order));

        } catch (\Throwable $th) {

            DB::rollBack();
            throw $th;

        }

    }

    public function setOrderReached(Department $department, Order $order)
    {

        if ($this->ordersNotMatched($department, $order) && $order->status_id !== 4) {
            return response()->json(['message' => 'Order Not matched, Refreshing the page may help.'], 400);
        }

        DB::beginTransaction();

        try {

            $order->update([
                'status_id' => 5,
                'is_inprogress' => true,
            ]);
            
            $order->dispatchingHistories()->create();

            DB::commit();

            broadcast(new OrderReached($order));

            return response()->json($this->loadRelatedData($order));

        } catch (\Throwable $th) {

            DB::rollBack();
            throw $th;

        }

    }

    public function setOrderCompleted(Department $department, Order $order)
    {

        if ($this->ordersNotMatched($department, $order) && $order->status_id !== 5) {
            return response()->json(['message' => 'Order Not matched, Refreshing the page may help.'], 400);
        }

        DB::beginTransaction();
        
        try {

            $order->update([
                'status_id' => 6,
                'sort_number' => 0,
                'completed_at' => now(),
                'is_inprogress' => false,
            ]);
            $order->dispatchingHistories()->create();

            DB::commit();

            broadcast(new OrderCompleted($order));

            return response()->json($this->loadRelatedData($order));

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    private function ordersNotMatched(Department $department, Order $order)
    {
        $inProgressOrder = Order::query()
            ->where('department_id', $department->id)
            ->where('technician_id', request()->user()->id)
            ->where('is_inprogress', true)
            ->first();

        if ($inProgressOrder && $inProgressOrder->id !== $order->id) {
            return true;
        }

        return false;
    }

}
