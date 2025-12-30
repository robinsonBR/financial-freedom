<?php

namespace Modules\Transaction\Policies;

use App\Models\User;
use Modules\Transaction\Models\Transaction;

class TransactionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
