<?php

namespace App\Services\Budget;

use Illuminate\Support\Collection;
use Modules\Transaction\Models\Transaction;

/**
 * Service for aggregating budget data.
 * Replaces client-side TypeScript logic from resources/js/Domain/budget/aggregateBudget.ts
 */
class BudgetAggregationService
{
    /**
     * Aggregate actual spending by category for a specific month.
     *
     * @param int $userId
     * @param int $year
     * @param int $month
     * @return array<string, float>
     */
    public function aggregateActualByCategory(int $userId, int $year, int $month): array
    {
        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $result = [];

        foreach ($transactions as $transaction) {
            $key = $transaction->category_id ?? 'uncategorized';
            
            if (!isset($result[$key])) {
                $result[$key] = 0.0;
            }
            
            $result[$key] += (float) $transaction->amount;
        }

        return $result;
    }

    /**
     * Get category budgets with actual spending.
     *
     * @param int $userId
     * @param int $year
     * @param int $month
     * @return Collection
     */
    public function getCategoryBudgets(int $userId, int $year, int $month): Collection
    {
        $actualByCategory = $this->aggregateActualByCategory($userId, $year, $month);

        return \App\Models\Category::query()
            ->where('user_id', $userId)
            ->get()
            ->map(function ($category) use ($actualByCategory) {
                $categoryKey = (string) $category->id;
                
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'planned' => (float) $category->monthly_budget,
                    'actual' => $actualByCategory[$categoryKey] ?? 0.0,
                    'remaining' => (float) $category->monthly_budget - ($actualByCategory[$categoryKey] ?? 0.0),
                ];
            });
    }

    /**
     * Get budget summary for a month.
     *
     * @param int $userId
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getMonthSummary(int $userId, int $year, int $month): array
    {
        $categoryBudgets = $this->getCategoryBudgets($userId, $year, $month);

        $totalPlanned = $categoryBudgets->sum('planned');
        $totalActual = $categoryBudgets->sum('actual');

        return [
            'total_planned' => $totalPlanned,
            'total_actual' => $totalActual,
            'total_remaining' => $totalPlanned - $totalActual,
            'utilization_percentage' => $totalPlanned > 0 
                ? round(($totalActual / $totalPlanned) * 100, 2)
                : 0,
            'categories' => $categoryBudgets,
        ];
    }
}
