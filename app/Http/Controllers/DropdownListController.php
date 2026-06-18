<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\ChartOfAccount;

class DropdownListController extends Controller
{
    public function permissions(Request $request)
    {
        $query = Permission::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        return response()->json($query->get());
    }

    public function roles(Request $request)
    {
        $query = Role::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        return response()->json($query->get());
    }

    public function departments(Request $request)
    {
        $query = Department::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        if($request->boolean('is_service_department')) {
            $query->where('is_service_department', true);
        }
        return response()->json($query->get());
    }

    public function products(Request $request)
    {
        $query = Product::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        return response()->json($query->get());
    }

    public function categories(Request $request)
    {
        $query = Category::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        return response()->json($query->get());
    }

    public function warehouses(Request $request)
    {
        $query = Warehouse::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        if($request->boolean('only_allowed')) {
            $query->whereHas('users', function ($q) {
                $q->where('users.id', request()->user()->id);
            });
        }
        return response()->json($query->get());
    }

    public function users(Request $request)
    {
        $query = User::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        return response()->json($query->get());
    }

    public function chartsOfAccounts(Request $request)
    {
        $query = ChartOfAccount::query();
        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }
        if($request->boolean('is_leaf')) {
            $query->where('is_leaf', true);
        }
        return response()->json($query->get());
    }


}
