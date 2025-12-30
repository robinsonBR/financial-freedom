<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    /**
     * Get all categories for a user grouped by group.
     */
    public function getAllForUserGrouped(int $userId): Collection
    {
        return Category::query()
            ->where('user_id', $userId)
            ->with('group')
            ->orderBy('group_id')
            ->orderBy('name')
            ->get()
            ->groupBy('group_id');
    }

    /**
     * Get categories with their budget totals.
     */
    public function getWithBudgetTotals(int $userId): Collection
    {
        return Category::query()
            ->where('user_id', $userId)
            ->with('group')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update a category.
     */
    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
