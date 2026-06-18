<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;

class CategoryController
{
    protected array $with = ['parent'];

    protected array $searchable = ['name_en', 'name_ar'];

    public function index(Request $request)
    {
        if(!request()->user()->hasPermission('categories_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض الفئات', 
                    'en' => 'You do not have permission to view categories'
                ],
            ], 403);
        }

        $query = Category::query()
            ->with($this->with)
            ->orderBy('id', 'desc');

        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }

        if($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if($request->boolean('is_active')) {
            $query->where('is_active', true);
        }

        $categories = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $categories->items(),
            'current_page' => $categories->currentPage(),
            'last_page' => $categories->lastPage(),
            'next_page_url' => $categories->nextPageUrl(),
            'per_page' => $categories->perPage(),
            'total' => $categories->total(),
            'can_create' => request()->user()->hasPermission('categories_create'),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json($category->load($this->with));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json($category->load($this->with));
    }

    public function destroy(Category $category)
    {
        if(!request()->user()->hasPermission('categories_delete')) {
            return response()->json(['message' => 'ليس لديك صلاحية لحذف الأقسام'], 403);
        }

        $category->delete();

        return response()->json($category->load($this->with));
    }
}
