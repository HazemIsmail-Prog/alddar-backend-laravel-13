<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Requests\Departments\StoreDepartmentRequest;
use App\Http\Requests\Departments\UpdateDepartmentRequest;
use Illuminate\Database\Eloquent\Model;

class DepartmentController
{
    protected array $searchable = ['name_en', 'name_ar'];

    private function loadRelatedData(Model $model): Model
    {
        return $model
            ->append('can_update')
            ->append('can_delete')
        ;
    }

    public function index(Request $request)
    {

        if(!request()->user()->hasPermission('departments_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض الأقسام', 
                    'en' => 'You do not have permission to view departments'
                ],
            ], 403);
        }

        $query = Department::query()
            ->orderBy('id', 'asc');

            if($request->has('search')) {
                $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
            }

            if($request->boolean('is_active')) {
                $query->where('is_active', true);
            }

            if($request->boolean('is_not_active')) {
                $query->where('is_active', false);
            }

            if($request->boolean('is_service_department')) {
                $query->where('is_service_department', true);
            }

            if($request->boolean('is_not_service_department')) {
                $query->where('is_service_department', false);
            }
        
        $departments = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => collect($departments->items())->map(fn($department) => $this->loadRelatedData($department)),
            'current_page' => $departments->currentPage(),
            'last_page' => $departments->lastPage(),
            'next_page_url' => $departments->nextPageUrl(),
            'per_page' => $departments->perPage(),
            'total' => $departments->total(),
            'can_create' => request()->user()->hasPermission('departments_create'),
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $department = Department::create($request->validated());

        return response()->json($this->loadRelatedData($department));
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return response()->json($this->loadRelatedData($department));
    }

    public function destroy(Department $department)
    {
        if(!request()->user()->hasPermission('departments_delete')) {
            return response()->json(['message' => 'ليس لديك صلاحية لحذف الأقسام'], 403);
        }

        $department->delete();

        return response()->json($this->loadRelatedData($department));
    }
}
