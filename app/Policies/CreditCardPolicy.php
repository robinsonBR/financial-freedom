<?php

namespace App\Policies;

use App\Models\CreditCard;
use App\Models\User;

class CreditCardPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CreditCard $creditCard): bool
    {
        return $user->id === $creditCard->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CreditCard $creditCard): bool
    {
        return $user->id === $creditCard->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CreditCard $creditCard): bool
    {
        return $user->id === $creditCard->user_id;
    }
}
