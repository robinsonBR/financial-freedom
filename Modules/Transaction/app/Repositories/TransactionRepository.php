<?php

namespace Modules\Transaction\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Transaction\Models\Transaction;

class TransactionRepository
{
    /**
     * Get paginated transactions for a user with filters.
     */
    public function getPaginatedForUser(
        int $userId,
        array $filters = [],
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = Transaction::query()
            ->where('user_id', $userId)
            ->with(['category', 'accountable']);

        // Apply date filters
        if (!empty($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Apply account filter
        if (!empty($filters['accountable_id']) && !empty($filters['accountable_type'])) {
            $query->where('accountable_id', $filters['accountable_id'])
                  ->where('accountable_type', $filters['accountable_type']);
        }

        // Apply type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Apply reconciliation filter
        if (isset($filters['reconciled'])) {
            $query->where('reconciled', $filters['reconciled']);
        }

        return $query->orderBy('date', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate($perPage);
    }

    /**
     * Get transactions summary for a date range.
     */
    public function getSummaryForDateRange(int $userId, string $startDate, string $endDate): array
    {
        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return [
            'total_income' => $transactions->where('type', 'income')->sum('amount'),
            'total_expense' => $transactions->where('type', 'expense')->sum('amount'),
            'net_amount' => $transactions->sum('amount'),
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Get transactions by category for a date range.
     */
    public function getByCategoryForDateRange(
        int $userId,
        string $startDate,
        string $endDate
    ): Collection {
        return Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('category')
            ->get()
            ->groupBy('category_id');
    }

    /**
     * Create a new transaction.
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update a transaction.
     */
    public function update(Transaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    /**
     * Delete a transaction.
     */
    public function delete(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    /**
     * Bulk create transactions.
     */
    public function bulkCreate(array $transactions): void
    {
        Transaction::insert($transactions);
    }
}
