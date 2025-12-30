<?php

namespace App\Services\Budget;

use App\Models\Category;
use Illuminate\Support\Collection;
use Modules\Transaction\Models\Transaction;

/**
 * Service for calculating budget utilization.
 * Replaces client-side TypeScript logic from resources/js/Domain/budget/utilization.ts
 */
class BudgetUtilizationService
{
    /**
     * Calculate budget utilization for categories.
     *
     * @param int $userId
     * @param int $year
     * @param int $month
     * @return Collection
     */
    public function getCategoryUtilization(int $userId, int $year, int $month): Collection
    {
        $categories = Category::query()
            ->where('user_id', $userId)
            ->with(['transactions' => function ($query) use ($year, $month) {
                $query->whereYear('date', $year)
                      ->whereMonth('date', $month);
            }])
            ->get();

        return $categories->map(function ($category) {
            $actual = $category->transactions->sum('amount');
            $planned = (float) $category->monthly_budget;

            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'category_color' => $category->color,
                'planned' => $planned,
                'actual' => (float) $actual,
                'remaining' => $planned - $actual,
                'utilization_percentage' => $planned > 0 
                    ? round(($actual / $planned) * 100, 2)
                    : 0,
                'is_over_budget' => $actual > $planned,
                'status' => $this->getUtilizationStatus($actual, $planned),
            ];
        });
    }

    /**
     * Get utilization status based on percentage.
     *
     * @param float $actual
     * @param float $planned
     * @return string
     */
    private function getUtilizationStatus(float $actual, float $planned): string
    {
        if ($planned <= 0) {
            return 'no-budget';
        }

        $percentage = ($actual / $planned) * 100;

        return match (true) {
            $percentage >= 100 => 'over-budget',
            $percentage >= 90 => 'warning',
            $percentage >= 70 => 'moderate',
            default => 'healthy',
        };
    }

    /**
     * Get categories that are over budget.
     *
     * @param int $userId
     * @param int $year
     * @param int $month
     * @return Collection
     */
    public function getOverBudgetCategories(int $userId, int $year, int $month): Collection
    {
        return $this->getCategoryUtilization($userId, $year, $month)
            ->where('is_over_budget', true)
            ->sortByDesc('actual');
    }

    /**
     * Get budget health score (0-100).
     *
     * @param int $userId
     * @param int $year
     * @param int $month
     * @return float
     */
    public function getHealthScore(int $userId, int $year, int $month): float
    {
        $utilization = $this->getCategoryUtilization($userId, $year, $month);

        if ($utilization->isEmpty()) {
            return 100.0;
        }

        // Calculate average utilization percentage
        $averageUtilization = $utilization->avg('utilization_percentage');

        // Health score: 100 at 0% utilization, 0 at 200% utilization
        $healthScore = max(0, 100 - ($averageUtilization / 2));

        return round($healthScore, 2);
    }
}
