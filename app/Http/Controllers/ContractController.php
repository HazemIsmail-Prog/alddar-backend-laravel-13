<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use App\Http\Requests\Contracts\StoreContractRequest;
use App\Http\Requests\Contracts\UpdateContractRequest;

class ContractController extends Controller
{
    protected array $searchable = ['contract_number', 'contract_type', 'contract_value', 'contract_date', 'contract_expiration_date', 'compressor_warranty_start_date', 'compressor_warranty_end_date', 'parts_status', 'contract_status', 'contract_payment_status'];
    protected array $with = ['party','creator'];

    public function index(Request $request)
    {

        if(!request()->user()->hasPermission('contracts_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض العقود', 
                    'en' => 'You do not have permission to view contracts'
                ],
            ], 403);
        }

        $query = Contract::query()
            ->with($this->with)
            ->orderBy('id', 'desc');

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        $contracts = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $contracts->items(),
            'current_page' => $contracts->currentPage(),
            'last_page' => $contracts->lastPage(),
            'next_page_url' => $contracts->nextPageUrl(),
            'per_page' => $contracts->perPage(),
            'total' => $contracts->total(),
            'can_create' => request()->user()->hasPermission('contracts_create'),
        ]);
    }

    public function store(StoreContractRequest $request)
    {
        $contract = Contract::create($request->validated());

        return response()->json($contract);
    }

    public function update(Contract $contract, UpdateContractRequest $request)
    {
        $contract->update($request->validated());

        return response()->json($contract);
    }

    public function destroy(Contract $contract)
    {
        if(!request()->user()->hasPermission('contracts_delete')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لحذف العقود', 
                    'en' => 'You do not have permission to delete contracts'
                ],
            ], 403);
        }

        $contract->delete();

        return response()->json($contract);
    }
}
