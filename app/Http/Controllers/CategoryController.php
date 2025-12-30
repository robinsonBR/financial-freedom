<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Categories\DeleteCategory;
use App\Services\Categories\StoreCategory;
use App\Services\Categories\UpdateCategory;
use App\Services\Groups\IndexGroups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(
        private readonly IndexGroups $indexGroups,
        private readonly StoreCategory $storeCategory,
        private readonly UpdateCategory $updateCategory,
        private readonly DeleteCategory $deleteCategory,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Settings/Categories/Index', [
            'group' => 'settings',
            'subGroup' => 'categories',
            'groups' => fn() => $this->indexGroups->index($request),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->storeCategory->store($request);
        
        return redirect()->back();
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->updateCategory->update($request, $category);
        
        return redirect()->back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->deleteCategory->delete($category);
        
        return redirect()->back();
    }
}
