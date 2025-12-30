<?php

namespace App\Repositories;

use App\Models\CashAccount;
use Illuminate\Database\Eloquent\Collection;

class CashAccountRepository
{
    /**
     * Get all cash accounts for a user with their relationships.
     */
    public function getAllForUser(int $userId): Collection
    {
        return CashAccount::query()
            ->where('user_id', $userId)
            ->with(['institution', 'rules'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get cash accounts with balance above a threshold.
     */
    public function getWithBalanceAbove(int $userId, float $threshold): Collection
    {
        return CashAccount::query()
            ->where('user_id', $userId)
            ->where('balance', '>', $threshold)
            ->with('institution')
            ->orderBy('balance', 'desc')
            ->get();
    }

    /**
     * Get total balance across all cash accounts for a user.
     */
    public function getTotalBalance(int $userId): float
    {
        return CashAccount::query()
            ->where('user_id', $userId)
            ->sum('balance');
    }

    /**
     * Create a new cash account.
     */
    public function create(array $data): CashAccount
    {
        return CashAccount::create($data);
    }

    /**
     * Update a cash account.
     */
    public function update(CashAccount $cashAccount, array $data): bool
    {
        return $cashAccount->update($data);
    }

    /**
     * Delete a cash account.
     */
    public function delete(CashAccount $cashAccount): bool
    {
        return $cashAccount->delete();
    }
}
