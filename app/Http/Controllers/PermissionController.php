<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\Permissions\StorePermissionRequest;
use App\Http\Requests\Permissions\UpdatePermissionRequest;

class PermissionController
{
    protected array $searchable = ['value', 'name_en', 'name_ar'];
    protected array $with = [];

    public function index(Request $request)
    {

        if(!request()->user()->hasPermission('permissions_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض الصلاحيات', 
                    'en' => 'You do not have permission to view permissions'
                ],
            ], 403);
        }

        $query = Permission::query()
            ->with($this->with)
            ->orderBy('id', 'desc');

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        if($request->has('is_active')) {
            $query->where('is_active', true);
        }

        $permissions = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $permissions->items(),
            'current_page' => $permissions->currentPage(),
            'last_page' => $permissions->lastPage(),
            'next_page_url' => $permissions->nextPageUrl(),
            'per_page' => $permissions->perPage(),
            'total' => $permissions->total(),
            'can_create' => request()->user()->hasPermission('permissions_create'),
        ]);
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = Permission::create($request->validated());

        return response()->json($permission);
    }

    public function update(Permission $permission, UpdatePermissionRequest $request)
    {
        $permission->update($request->validated());

        return response()->json($permission);
    }

    public function destroy(Permission $permission)
    {
        if(!request()->user()->hasPermission('permissions_delete')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لحذف الصلاحيات', 
                    'en' => 'You do not have permission to delete permissions'
                ],
            ], 403);
        }

        $permission->delete();

        return response()->json($permission);
    }
}
