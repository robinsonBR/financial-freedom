<?php

namespace App\Services\Groups;

use App\Models\Group;

class UpdateGroup
{
    public function update($request, Group $group): Group
    {
        $group->update([
            'name' => $request->input('name'),
            'color' => $request->input('color'),
        ]);

        return $group->fresh();
    }
}
