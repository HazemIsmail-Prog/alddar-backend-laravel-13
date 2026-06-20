<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;

class RoleController
{
    protected array $with = ['permissions'];
    protected array $searchable = ['name_en', 'name_ar'];

    public function index(Request $request)
    {
        if(!request()->user()->hasPermission('roles_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض الأدوار', 
                    'en' => 'You do not have permission to view roles'
                ],
            ], 403);
        }

        $query = Role::query()
            ->with($this->with)
            ->orderBy('id', 'desc');

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        if($request->has('permissions')) {
            $query->whereHas('permissions', function ($q) use ($request) {
                $q->whereIn('permissions.id', $request->permissions);
            });
        }
        
        $roles = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $roles->items(),
            'current_page' => $roles->currentPage(),
            'last_page' => $roles->lastPage(),
            'next_page_url' => $roles->nextPageUrl(),
            'per_page' => $roles->perPage(),
            'total' => $roles->total(),
            'can_create' => request()->user()->hasPermission('roles_create'),
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::create($request->validated());
            $role->permissions()->sync($request->permissions);
            DB::commit();

            return response()->json($role->load('permissions'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Role $role, UpdateRoleRequest $request)
    {
        DB::beginTransaction();
        try {
            $role->update($request->validated());
            $role->permissions()->sync($request->permissions);
            // delete all tokens for the selected role users
            $role->users()->each(function ($user) {
                $user->tokens()->delete();
            });

            DB::commit();

            return response()->json($role->load('permissions'));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Role $role)
    {
        if(!request()->user()->hasPermission('roles_delete')) {
            return response()->json(['message' => 'ليس لديك صلاحية لحذف الأدوار'], 403);
        }

        $role->delete();

        return response()->json($role);
    }
}
