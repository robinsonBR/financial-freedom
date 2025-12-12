<?php

namespace App\Services\Categories;

use App\Models\Category;

class StoreCategory
{
    public function store( $request )
    {
        $category = Category::create([
            'user_id' => $request->user()->id,
            'group_id' => $request->input('group_id'),
            'name' => $request->input('name'),
            'color' => $request->input('color'),
            'monthly_budget' => $request->input('monthly_budget') ?? 0,
        ]);

        return $category;
    }
}