<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Requests\Warehouses\UpdateWarehouseRequest;
use App\Http\Requests\Warehouses\StoreWarehouseRequest;

class WarehouseController
{
    protected array $searchable = ['name_en', 'name_ar'];

    protected array $with = [];

    public function index(Request $request)
    {

        if(!request()->user()->hasPermission('warehouses_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض المخازن', 
                    'en' => 'You do not have permission to view warehouses'
                ],
            ], 403);
        }

        $query = Warehouse::query()
            ->with($this->with)
            ->orderBy('id', 'desc');

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        $warehouses = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $warehouses->items(),
            'current_page' => $warehouses->currentPage(),
            'last_page' => $warehouses->lastPage(),
            'next_page_url' => $warehouses->nextPageUrl(),
            'per_page' => $warehouses->perPage(),
            'total' => $warehouses->total(),
            'can_create' => request()->user()->hasPermission('warehouses_create'),
        ]);

    }

    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = Warehouse::create($request->validated());

        return response()->json($warehouse->load($this->with));
    }
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return response()->json($warehouse->load($this->with));
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json($warehouse);
    }
}
