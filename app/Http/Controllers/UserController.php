<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;

class UserController
{
    protected array $with = ['permissions:id', 'roles:id,name_en,name_ar', 'departments:id,name_en,name_ar', 'warehouses:id,name_en,name_ar'];

    protected array $searchable = ['name_en', 'name_ar', 'email', 'civil_id'];
    protected array $select = ['id', 'name_en', 'name_ar', 'email', 'civil_id', 'is_technician', 'is_active'];

    public function index(Request $request)
    {
        if(!request()->user()->hasPermission('users_view')) {
            return response()->json(['message' => 'ليس لديك صلاحية لعرض المستخدمين'], 403);
        }
        
        $query = User::query()
            ->select($this->select)
            ->with($this->with)
            ->orderBy('id', 'desc');

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        if($request->boolean('is_technician')) {
            $query->where('is_technician', true);
        }

        if($request->boolean('is_not_technician')) {
            $query->where('is_technician', false);
        }
        
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        
        if($request->boolean('is_not_active')) {
            $query->where('is_active', false);
        }
        
        if($request->has('departments')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->whereIn('departments.id', $request->departments);
            });
        }

        if($request->has('warehouses')) {
            $query->whereHas('warehouses', function ($q) use ($request) {
                $q->whereIn('warehouses.id', $request->warehouses);
            });
        }

        if($request->has('permissions')) {
            $permissionCount = is_array($request->permissions) ? count($request->permissions) : 0;
            if ($permissionCount > 0) {
                $query->where(function ($q) use ($request, $permissionCount) {
                    // Users with all permissions directly
                    $q->whereHas('permissions', function ($q2) use ($request) {
                        $q2->whereIn('permissions.id', $request->permissions);
                    }, '=', $permissionCount)
                    // OR users with all permissions via roles
                        ->orWhereHas('roles', function ($q3) use ($request, $permissionCount) {
                            $q3->whereHas('permissions', function ($q4) use ($request) {
                                $q4->whereIn('permissions.id', $request->permissions);
                            }, '=', $permissionCount);
                        });
                });
            }
        }

        $users = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $users->items(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'next_page_url' => $users->nextPageUrl(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'can_create' => request()->user()->hasPermission('users_create'),
        ]);

    }

    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::create($request->validated());
            if ($request->has('permissions')) {
                $user->permissions()->sync($request->permissions);
            }
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            }
            if ($request->has('departments')) {
                $user->departments()->sync($request->departments);
            }
            if ($request->has('warehouses')) {
                $user->warehouses()->sync($request->warehouses);
            }
            DB::commit();

            return response()->json($user->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(User $user, UpdateUserRequest $request)
    {

        $validated = $request->validated();
        if ($request->password) {
            $validated['password'] = bcrypt($request->password);
        }
        DB::beginTransaction();

        try {

            $user->update($validated);

            if ($request->has('permissions')) {
                // Get the original permissions as a sorted array of IDs
                $originalPermissions = $user->permissions()->pluck('id')->sort()->values()->toArray();
                // Get the requested permissions as a sorted array of IDs
                $newPermissions = collect($request->permissions)->sort()->values()->toArray();

                // Sync the new permissions
                $user->permissions()->sync($request->permissions);

                // Delete tokens only if permissions have actually changed
                if ($originalPermissions !== $newPermissions) {
                    $user->tokens()->delete();
                }
            }
            
            if ($request->has('roles')) {
                // Get the original roles as a sorted array of IDs
                $originalRoles = $user->roles()->pluck('id')->sort()->values()->toArray();
                // Get the requested roles as a sorted array of IDs
                $newRoles = collect($request->roles)->sort()->values()->toArray();

                // Sync the new roles
                $user->roles()->sync($request->roles);

                // Delete tokens only if roles have actually changed
                if ($originalRoles !== $newRoles) {
                    $user->tokens()->delete();
                }
            }

            if ($request->has('departments')) {
                $user->departments()->sync($request->departments);
            }

            if ($request->has('warehouses')) {
                $user->warehouses()->sync($request->warehouses);
            }

            DB::commit();

            return response()->json($user->load($this->with));

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);

        }
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json($user);
    }
}
