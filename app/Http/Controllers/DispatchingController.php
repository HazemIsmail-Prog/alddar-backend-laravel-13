<?php

namespace App\Http\Controllers;

use App\Events\Orders\OrderHolded;
use App\Events\Orders\OrderAssigned;
use App\Events\Orders\OrderUnassigned;
use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderReceived;
use App\Events\Orders\OrderReached;
use App\Events\Orders\OrderCompleted;
use App\Events\Orders\OrderMoved;
use App\Services\DispatchingService;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchingController
{
    protected array $with = [
        'party.locations',
        'party.phones',
        'items',
        'department',
        'technician',
        'status',
        'location',
        'phone',
        'invoices.payments',
    ];

    public function getTechnicianOrders(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
        ]);

        // if auth technician has in progress order, return it
        if ($request->user()->orders->where('is_inprogress', true)->count() > 0) {
            return response()->json($request->user()->orders->where('is_inprogress', true)->first()->load($this->with));
        }

        // return minimum sort number order
        $minimumSortNumberOrder = Order::query()
            ->where('department_id', $request->department_id)
            ->where('technician_id', $request->user()->id)
            ->whereIn('status_id', [3, 4, 5])
            ->orderBy('sort_number', 'asc')
            ->first();

        if (!$minimumSortNumberOrder) {
            return response()->json(null);
        }

        return response()->json($minimumSortNumberOrder->load($this->with));
    }

    public function index(Department $department)
    {

        $orderStatuses = OrderStatus::query()
            ->select(['id', 'name', 'color'])
            ->get();

        $technicians = User::query()
            ->whereHas('departments', function ($query) use ($department) {
                $query->where('departments.id', $department->id);
            })
            ->withCount([
                'orders as todays_completed_orders_count' => function ($query) use ($department) {
                    $query->where('department_id', $department->id)
                        ->whereDate('completed_at', now()->toDateString())
                        ->where('status_id', 6);
                },
            ])

            ->where('is_technician', true)
            ->where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();

        $todayCompletedOrdersCount = Order::query()
            ->where('department_id', $department->id)
            ->whereDate('completed_at', now()->toDateString())
            ->where('status_id', 6)
            ->count();

        $todayCancelledOrdersCount = Order::query()
            ->where('department_id', $department->id)
            ->whereDate('cancelled_at', now()->toDateString())
            ->where('status_id', 7)
            ->count();

        $orders = Order::query()
            ->with($this->with)
            ->where('department_id', $department->id)
            ->where('is_confirmed_to_dispatch', true)
            ->whereIn('status_id', [1, 2, 3, 4, 5])
            ->select([
                'id',
                'order_number',
                'party_id',
                'department_id',
                'status_id',
                'technician_id',
                'location_id',
                'phone_id',
                'notes',
                'sort_number',
                'order_date',
                'is_inprogress',
                'is_confirmed_to_dispatch',
            ])
            ->get();

        return response()->json([
            'orders' => $orders,
            'technicians' => $technicians,
            'orderStatuses' => $orderStatuses,
            'todayCompletedOrdersCount' => $todayCompletedOrdersCount,
            'todayCancelledOrdersCount' => $todayCancelledOrdersCount,
        ]);
    }

    public function setOrderUnassigned(Order $order, Request $request)
    {
        $request->validate([
            'sortNumber' => 'required|numeric',
        ]);
        
        $oldTechnicianId = $order->technician_id;
        $firstOrderIdBeforeUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);

        DB::beginTransaction();
        try {

            // update order
            $order->update([
                'sort_number' => $request->sortNumber,
                'status_id' => 1,
                'technician_id' => null,
                'is_inprogress' => false,
            ]);

            // create dispatching history
            $order->dispatchingHistories()->create();

            DB::commit();

            $firstOrderIdAfterUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);
            if($firstOrderIdAfterUpdate === $firstOrderIdBeforeUpdate) {
                // clear old Technician ID to prevent dispatching to the old technician
                $oldTechnicianId = null;
            }

            broadcast(new OrderUnassigned($order->load($this->with), $oldTechnicianId));
    
            return response()->json($order->refresh());

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function setOrderHolded(Order $order, Request $request)
    {
        $request->validate([
            'sortNumber' => 'required|numeric',
        ]);
        $oldTechnicianId = $order->technician_id;
        $firstOrderIdBeforeUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);

        DB::beginTransaction();
        try {
            // update order
            $order->update([
                'sort_number' => $request->sortNumber,
                'status_id' => 2,
                'technician_id' => null,
                'is_inprogress' => false,
            ]);
            
            // create dispatching history
            $order->dispatchingHistories()->create();

            DB::commit();
            
            $firstOrderIdAfterUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);
            if($firstOrderIdAfterUpdate === $firstOrderIdBeforeUpdate) {
                // clear old Technician ID to prevent dispatching to the old technician
                $oldTechnicianId = null;
            }
            broadcast(new OrderHolded($order->load($this->with), $oldTechnicianId));
    
            return response()->json($order->refresh());

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            throw $th;
        }

    }

    public function setOrderCancelled(Order $order, Request $request)
    {
        $oldTechnicianId = $order->technician_id;
        $firstOrderIdBeforeUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);

        DB::beginTransaction();
        try {
            // update order
            $order->update([
                'sort_number' => 0,
                'status_id' => 7,
                'technician_id' => null,
                'cancelled_at' => now(),
                'is_inprogress' => false,
            ]);
            
            // create dispatching history
            $order->dispatchingHistories()->create();

            DB::commit();

            $firstOrderIdAfterUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);
            if($firstOrderIdAfterUpdate === $firstOrderIdBeforeUpdate) {
                // clear old Technician ID to prevent dispatching to the old technician
                $oldTechnicianId = null;
            }

            broadcast(new OrderCancelled($order->load($this->with), $oldTechnicianId));

            return response()->json($order->refresh());

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function assignTechnician(Order $order, Request $request)
    {
        $request->validate([
            'technicianId' => 'required|exists:users,id',
            'sortNumber' => 'required|numeric',
        ]);
        $oldTechnicianId = $order->technician_id;
        $newTechnicianId = $request->technicianId;

        $firstOrderIdBeforeUpdateForOldTechnician = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);
        $firstOrderIdBeforeUpdateForNewTechnician = DispatchingService::getFirstOrderIdForTechnicianIndepartment($newTechnicianId, $order->department_id);

        DB::beginTransaction();
        try {
            //code...
            $order->update([
                'sort_number' => $request->sortNumber,
                'status_id' => 3,
                'technician_id' => $newTechnicianId,
                'is_inprogress' => false,
            ]);

            // create dispatching history
            $order->dispatchingHistories()->create();

            DB::commit();
            
            $firstOrderIdAfterUpdateForOldTechnician = DispatchingService::getFirstOrderIdForTechnicianIndepartment($oldTechnicianId, $order->department_id);
            $firstOrderIdAfterUpdateForNewTechnician = DispatchingService::getFirstOrderIdForTechnicianIndepartment($newTechnicianId, $order->department_id);
            
            if($firstOrderIdAfterUpdateForOldTechnician === $firstOrderIdBeforeUpdateForOldTechnician) {
                // clear old Technician ID to prevent dispatching to the old technician
                $oldTechnicianId = null;
            }
            if($firstOrderIdAfterUpdateForNewTechnician === $firstOrderIdBeforeUpdateForNewTechnician) {
                // clear new Technician ID to prevent dispatching to the new technician
                $newTechnicianId = null;
            }
            broadcast(new OrderAssigned($order->load($this->with), $oldTechnicianId, $newTechnicianId));
    
            return response()->json($order->refresh());

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function setOrderReceived(Order $order)
    {
        // abort if technician has another inprogress order
        if ($order->technician->orders->where('is_inprogress', true)->count() > 0) {
            return response()->json(['message' => 'Technician has another inprogress order'], 400);
        }

        DB::beginTransaction();
        try {
            //update order
            $order->update([
                'status_id' => 4,
                'is_inprogress' => true,
            ]);
            
            // create dispatching history
            $order->dispatchingHistories()->create();

            DB::commit();

            broadcast(new OrderReceived($order->load($this->with)));
            return response()->json($order->refresh());
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            //throw $th;
        }

    }

    public function setOrderReached(Order $order)
    {
        // abort if technician has another inprogress order
        if ($order->status_id !== 4 || !$order->is_inprogress) {
            return response()->json(['message' => 'Order is not received'], 400);
        }

        DB::beginTransaction();
        try {
            //code...
            $order->update([
                'status_id' => 5,
                'is_inprogress' => true,
            ]);
            
            // create dispatching history
            $order->dispatchingHistories()->create();

            DB::commit();

            broadcast(new OrderReached($order->load($this->with)));
            return response()->json($order->refresh());

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }


    }

    public function setOrderCompleted(Order $order)
    {
        if ($order->status_id !== 5 || !$order->is_inprogress) {
            return response()->json(['message' => 'Order is not reached'], 400);
        }

        DB::beginTransaction();
        try {
            //code...
            $order->update([
                'status_id' => 6,
                'sort_number' => 0,
                'completed_at' => now(),
                'is_inprogress' => false,
            ]);
            $order->dispatchingHistories()->create();

            DB::commit();

            broadcast(new OrderCompleted($order->load($this->with)));

            return response()->json($order->refresh());

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function updateOrderSortNumber(Order $order, Request $request)
    {
        $request->validate([
            'sortNumber' => 'required|numeric',
        ]);

        $technicianId = $order->technician_id;
        $firstOrderIdBeforeUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($order->technician_id, $order->department_id);

        $order->sort_number = $request->sortNumber;
        $order->save();
        $firstOrderIdAfterUpdate = DispatchingService::getFirstOrderIdForTechnicianIndepartment($order->technician_id, $order->department_id);
        if($firstOrderIdAfterUpdate === $firstOrderIdBeforeUpdate) {
            // clear Technician ID to prevent dispatching to the technician
            $technicianId = null;
        }
        broadcast(new OrderMoved($order->load($this->with), $technicianId));
        return response()->json($order->refresh());
    }
}
