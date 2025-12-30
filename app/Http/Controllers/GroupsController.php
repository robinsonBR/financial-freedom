<?php

namespace App\Http\Controllers;

use App\Http\Requests\Groups\StoreGroupRequest;
use App\Http\Requests\Groups\UpdateGroupRequest;
use App\Models\Group;
use App\Services\Groups\DeleteGroup;
use App\Services\Groups\StoreGroup;
use App\Services\Groups\UpdateGroup;
use Illuminate\Http\RedirectResponse;

class GroupsController extends Controller
{
    public function __construct(
        private readonly StoreGroup $storeGroup,
        private readonly UpdateGroup $updateGroup,
        private readonly DeleteGroup $deleteGroup,
    ) {}

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $this->storeGroup->store($request);
        
        return redirect()->back();
    }

    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $this->updateGroup->update($request, $group);
        
        return redirect()->back();
    }

    public function destroy(Group $group): RedirectResponse
    {
        try {
            $this->deleteGroup->delete($group);
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
