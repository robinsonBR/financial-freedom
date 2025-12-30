<?php

namespace App\Services\Groups;

use App\Models\Group;

class DeleteGroup
{
    public function delete(Group $group): void
    {
        // Reassign categories to a default group or delete them
        // For now, we'll prevent deletion if group has categories
        if ($group->categories()->count() > 0) {
            throw new \Exception('Cannot delete a group that contains categories. Please move or delete the categories first.');
        }
        
        $group->delete();
    }
}
