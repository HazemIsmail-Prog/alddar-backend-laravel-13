<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ChartOfAccounts\StoreChartOfAccountRequest;
use App\Http\Requests\ChartOfAccounts\UpdateChartOfAccountRequest;

class ChartOfAccountController
{
    protected array $with = ['parent'];

    protected array $searchable = ['account_code', 'account_name'];

    public function index(Request $request)
    {
        $query = ChartOfAccount::query();

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        if($request->has('is_leaf')) {
            $query->where('is_leaf', true);
        }

        $query->orderBy('id', 'desc');

        $chartOfAccounts = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $chartOfAccounts->items(),
            'current_page' => $chartOfAccounts->currentPage(),
            'last_page' => $chartOfAccounts->lastPage(),
            'next_page_url' => $chartOfAccounts->nextPageUrl(),
            'per_page' => $chartOfAccounts->perPage(),
            'total' => $chartOfAccounts->total(),
            'can_create' => request()->user()->hasPermission('chart_of_accounts_create'),
        ]);
    }

    public function store(StoreChartOfAccountRequest $request)
    {
        DB::beginTransaction();
        try {

            $chartOfAccount = ChartOfAccount::create($request->validated());
            
            // if the parent is a leaf, update it to not be a leaf
            if ($chartOfAccount->parent && $chartOfAccount->parent->is_leaf) {
                $chartOfAccount->parent->update(['is_leaf' => false]);
            }

            DB::commit();

            return response()->json($chartOfAccount->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chartOfAccount)
    {
        // get the old parent before the update
        $oldParent = $chartOfAccount->parent;

        DB::beginTransaction();
        try {

            $chartOfAccount->update($request->validated());

            // if the new parent is a leaf, update it to not be a leaf
            if ($chartOfAccount->parent && $chartOfAccount->parent->is_leaf) {
                $chartOfAccount->parent->update(['is_leaf' => false]);
            }

            // if the old parent has no children, update it to be a leaf
            if ($oldParent && $oldParent->is_leaf && $oldParent->children->isEmpty()) {
                $oldParent->update(['is_leaf' => true]);
            }

            DB::commit();

            return response()->json($chartOfAccount->load($this->with));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->children->isNotEmpty()) {
            return response()->json([
                'message' => 'Cannot delete account that has child accounts.',
            ], 422);
        }

        if ($chartOfAccount->is_system_account) {
            return response()->json([
                'message' => 'Cannot delete system account.',
            ], 422);
        }

        $parent = $chartOfAccount->parent;
        $chartOfAccount->delete();

        // if the parent has no children, update it to be a leaf
        if ($parent && $parent->is_leaf && $parent->children->isEmpty()) {
            $parent->update(['is_leaf' => true]);
        }

        return response()->json($chartOfAccount);
    }
}
