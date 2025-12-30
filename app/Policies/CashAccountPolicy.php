<?php

namespace App\Policies;

use App\Models\CashAccount;
use App\Models\User;

class CashAccountPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CashAccount $cashAccount): bool
    {
        return $user->id === $cashAccount->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CashAccount $cashAccount): bool
    {
        return $user->id === $cashAccount->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CashAccount $cashAccount): bool
    {
        return $user->id === $cashAccount->user_id;
    }
}
