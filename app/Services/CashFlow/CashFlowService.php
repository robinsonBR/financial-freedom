<?php

namespace App\Services\CashFlow;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Carbon;
use Modules\Transaction\Models\Transaction;

class CashFlowService
{
	public function getMonthlyCashFlowPageData(User $user, ?Carbon $month = null): array
	{
		$now = $month ?? Carbon::now();

		$year = (int) $now->format('Y');
		$monthNumber = (int) $now->format('m');

		$startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
		$endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

		$transactions = Transaction::query()
			->where('user_id', $user->id)
			->whereBetween('date', [$startOfMonth, $endOfMonth])
			->orderBy('date', 'desc')
			->get(['id', 'date', 'amount', 'type', 'merchant', 'category_id']);

		$income = (float) $transactions->where('type', 'credit')->sum('amount');
		$expenses = (float) $transactions->where('type', 'debit')->sum('amount');
		$net = $income - $expenses;

		$categoryIds = $transactions
			->pluck('category_id')
			->filter()
			->unique()
			->values();

		$categories = Category::query()
			->whereIn('id', $categoryIds)
			->get(['id', 'name'])
			->keyBy('id');

		$categorySummary = $transactions
			->groupBy('category_id')
			->map(function ($group, $categoryId) use ($categories) {
				$income = $group->where('type', 'credit')->sum('amount');
				$expenses = $group->where('type', 'debit')->sum('amount');
				$net = $income - $expenses;

				$categoryIdInt = $categoryId !== null ? (int) $categoryId : null;
				$categoryName = $categoryIdInt !== null && isset($categories[$categoryIdInt])
					? $categories[$categoryIdInt]->name
					: 'Uncategorized';

				return [
					'category_id' => $categoryIdInt,
					'category_name' => $categoryName,
					'income' => (float) $income,
					'expenses' => (float) $expenses,
					'net' => (float) $net,
				];
			})
			->values();

		return [
			'year' => $year,
			'month' => $monthNumber,
			'summary' => [
				'income' => (float) $income,
				'expenses' => (float) $expenses,
				'net' => (float) $net,
			],
			'categorySummary' => $categorySummary,
			'transactions' => $transactions,
		];
	}

	public function getMonthlySummary(User $user, ?Carbon $month = null): array
	{
		$now = $month ?? Carbon::now();

		$startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
		$endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

		$monthlyTransactions = Transaction::query()
			->where('user_id', $user->id)
			->whereBetween('date', [$startOfMonth, $endOfMonth])
			->get(['amount', 'type']);

		$income = (float) $monthlyTransactions->where('type', 'credit')->sum('amount');
		$expenses = (float) $monthlyTransactions->where('type', 'debit')->sum('amount');
		$net = $income - $expenses;

		return [
			'income' => $income,
			'expenses' => $expenses,
			'net' => $net,
		];
	}
}
