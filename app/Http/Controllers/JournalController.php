<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Journals\StoreJournalRequest;
use App\Http\Requests\Journals\UpdateJournalRequest;
use App\Actions\UpdateLeafAccountsBalanceAction;

class JournalController
{
    protected array $with = ['entries.account'];

    protected array $searchable = ['journal_number', 'journal_type', 'reference_number', 'description', 'status'];

    public function index(Request $request)
    {
        if(!request()->user()->hasPermission('journals_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض اليوميات', 
                    'en' => 'You do not have permission to view journals'
                ],
            ], 403);
        }
        $query = Journal::query()
            ->with($this->with)
            ->orderBy('id', 'desc');
            if($request->has('search')) {
                $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
            }
            if($request->has('status')) {
                $query->where('status', $request->status);
            }
            $journals = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $journals->items(),
            'current_page' => $journals->currentPage(),
            'last_page' => $journals->lastPage(),
            'next_page_url' => $journals->nextPageUrl(),
            'per_page' => $journals->perPage(),
            'total' => $journals->total(),
            'can_create' => request()->user()->hasPermission('journals_create'),
        ]);
    }

    public function show(Journal $journal)
    {
        return response()->json($journal->load($this->with));
    }

    public function store(StoreJournalRequest $request)
    {

        if(!request()->user()->hasPermission('journals_create')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لإنشاء اليومية', 
                    'en' => 'You do not have permission to create the journal'
                ],
            ], 403);
        }

        $safe = $request->safe();
        $validatedJournalData = $safe->except('entries');
        $validatedEntriesData = $safe->input('entries', []);
        
        DB::beginTransaction();
        try {
            $journal = Journal::create($validatedJournalData);
            $journal->entries()->createMany($validatedEntriesData);
            (new UpdateLeafAccountsBalanceAction())->handle();
            DB::commit();
            return response()->json($journal->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function update(UpdateJournalRequest $request, Journal $journal)
    {
        if(!request()->user()->hasPermission('journals_update')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لتحديث اليومية', 
                    'en' => 'You do not have permission to update the journal'
                ],
            ], 403);
        }

        if($journal->reference_type) {
            return response()->json([
                'message' => [
                    'ar' =>'هذا القيد منشأ من قبل نظام النظام, لا يمكن تحديثه', 
                    'en' => 'This journal is created by the system, you cannot update it'
                ],
            ], 403);
        }

        $safe = $request->safe();
        $validatedJournalData = $safe->except('entries');
        $validatedEntriesData = $safe->input('entries', []);

        DB::beginTransaction();
        try {
            $journal->update($validatedJournalData);
            $journal->syncMany('entries', $validatedEntriesData);
            (new UpdateLeafAccountsBalanceAction())->handle();
            DB::commit();
            return response()->json($journal->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Journal $journal)
    {
        if(!request()->user()->hasPermission('journals_delete')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لحذف اليومية', 
                    'en' => 'You do not have permission to delete the journal'
                ],
            ], 403);
        }

        if($journal->reference_type) {
            return response()->json([
                'message' => [
                    'ar' =>'هذا القيد منشأ من قبل نظام النظام, لا يمكن حذفه', 
                    'en' => 'This journal is created by the system, you cannot delete it'
                ],
            ], 403);
        }


        DB::beginTransaction();
        try {
            $journal->entries()->delete();
            $journal->delete();
            (new UpdateLeafAccountsBalanceAction())->handle();
            DB::commit();
            return response()->json(['message' => 'Journal deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
