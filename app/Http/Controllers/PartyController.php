<?php

namespace App\Http\Controllers;

use App\Http\Requests\Parties\StorePartyRequest;
use App\Http\Requests\Parties\UpdatePartyRequest;
use App\Events\Parties\PartyCreated;
use App\Events\Parties\PartyUpdated;
use App\Events\Parties\PartyDeleted;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartyController
{
    protected array $searchable = ['name'];

    protected array $with = ['locations', 'phones'];

    public function index(Request $request)
    {

        if (!$request->boolean('is_client') && !$request->boolean('is_vendor')) {
            return response()->json([
                'message' => [
                    'ar' =>'يجب أن يكون أحدهما صحيحًا: is_client أو is_vendor',
                    'en' => 'One of them must be true: is_client or is_vendor'
                ],
            ], 422);
        }

        if(!request()->user()->hasPermission('clients_view') && $request->boolean('is_client')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض العملاء', 
                    'en' => 'You do not have permission to view clients'
                ],
            ], 403);
        }

        if(!request()->user()->hasPermission('vendors_view') && $request->boolean('is_vendor')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض الموردين', 
                    'en' => 'You do not have permission to view vendors'
                ],
            ], 403);
        }

        $query = Party::query()
            ->with($this->with)
            ->orderBy('id', 'desc');

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        if($request->boolean('is_client')) {
            $query->where('is_client', true);
        }

        if($request->boolean('is_vendor')) {
            $query->where('is_vendor', true);
        }

        $parties = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $parties->items(),
            'current_page' => $parties->currentPage(),
            'last_page' => $parties->lastPage(),
            'next_page_url' => $parties->nextPageUrl(),
            'per_page' => $parties->perPage(),
            'total' => $parties->total(),
            'can_create' => 
                $request->boolean('is_client') 
                    ? request()->user()->hasPermission('clients_create') 
                    : ($request->boolean('is_vendor') 
                        ? request()->user()->hasPermission('vendors_create') 
                        : false),
        ]);
    }

    public function store(StorePartyRequest $request)
    {

        DB::beginTransaction();
        try {
            $party = Party::create($request->except('locations', 'phones'));
            $party->syncMany('locations', $request->locations);
            $party->syncMany('phones', $request->phones);
            DB::commit();

            broadcast(new PartyCreated($party->load($this->with)))->toOthers();
            return response()->json($party->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();


            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdatePartyRequest $request, Party $party)
    {

        $validated = $request->safe();

        DB::beginTransaction();
        try {
            $party->update($validated->except('locations', 'phones'));
            $party->syncMany('locations', $validated['locations']);
            $party->syncMany('phones', $validated['phones']);
            DB::commit();

            broadcast(new PartyUpdated($party->load($this->with)))->toOthers();
            return response()->json($party->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Party $party)
    {
        if(!request()->user()->hasPermission('parties_delete')) {
            return response()->json(['message' => 'ليس لديك صلاحية لحذف الأطراف'], 403);
        }

        $party->delete();
        
        broadcast(new PartyDeleted($party->load($this->with)))->toOthers();

        return response()->json($party);
    }
}
