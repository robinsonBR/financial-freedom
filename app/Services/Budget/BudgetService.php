<?php

namespace App\Services\Budget;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Carbon;
use Modules\Transaction\Models\Transaction;

class BudgetService
{
	public function __construct(
		private readonly BudgetUtilizationService $utilization,
		private readonly BudgetAggregationService $aggregation,
	) {}

	public function getMonthlyBudgetPageData(User $user, ?Carbon $month = null): array
	{
		$now = $month ?? Carbon::now();

		$year = (int) $now->format('Y');
		$monthNumber = (int) $now->format('m');

		$categories = Category::query()
			->where('user_id', $user->id)
			->orderBy('name')
			->get(['id', 'name', 'monthly_budget']);

		$startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
		$endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

		$transactions = Transaction::query()
			->where('user_id', $user->id)
			->whereBetween('date', [$startOfMonth, $endOfMonth])
			->get(['category_id', 'amount', 'date']);

		// Calculate actual spending by category
		$actualByCategory = $this->aggregation->aggregateActualByCategory($transactions, $year, $monthNumber);

		// Build category rows with utilization
		$categoryRows = $categories->map(function ($category) use ($actualByCategory) {
			$actual = $actualByCategory[$category->id] ?? 0.0;
			$planned = $category->monthly_budget ?? 0.0;
			
			return [
				'categoryId' => $category->id,
				'name' => $category->name,
				'planned' => $planned,
				'actual' => $actual,
				'utilization' => $this->utilization->getCategoryUtilization($planned, $actual),
			];
		});

		return [
			'year' => $year,
			'month' => $monthNumber,
			'categories' => $categoryRows,
		];
	}

	public function getMonthlySummary(User $user, ?Carbon $month = null): array
	{
		$now = $month ?? Carbon::now();

		$startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
		$endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

		$plannedBudget = (float) Category::query()
			->where('user_id', $user->id)
			->sum('monthly_budget');

		$actualSpending = (float) Transaction::query()
			->where('user_id', $user->id)
			->whereBetween('date', [$startOfMonth, $endOfMonth])
			->where('type', 'debit')
			->sum('amount');

		$utilization = $this->utilization->getCategoryUtilization($plannedBudget, $actualSpending);

		return [
			'planned' => $plannedBudget,
			'actual' => $actualSpending,
			'utilization' => $utilization,
		];
	}
}
