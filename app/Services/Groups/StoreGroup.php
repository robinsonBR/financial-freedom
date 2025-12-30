<?php

namespace App\Services\Groups;

use App\Models\Group;

class StoreGroup
{
    public function store($request): Group
    {
        return Group::create([
            'user_id' => $request->user()->id,
            'name' => $request->input('name'),
            'color' => $request->input('color', 'blue'),
        ]);
    }
}
