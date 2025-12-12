<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CreditCard;
use App\Models\Loan;
use App\Services\Budget\BudgetService;
use App\Services\Goals\GoalsService;
use App\Services\CashFlow\CashFlowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
	public function __construct(
		private readonly GoalsService $goals,
		private readonly BudgetService $budget,
		private readonly CashFlowService $cashFlow,
	) {}

	public function index(Request $request): Response
	{
		$user = $request->user();

		$assets = (float) CashAccount::query()
			->where('user_id', $user->id)
			->sum('balance');

		$creditCardLiabilities = (float) CreditCard::query()
			->where('user_id', $user->id)
			->sum('balance');

		$loanLiabilities = (float) Loan::query()
			->where('user_id', $user->id)
			->sum('remaining_balance');

		$liabilities = $creditCardLiabilities + $loanLiabilities;

		$budgetSummary = $this->budget->getMonthlySummary($user);
		$cashFlowSummary = $this->cashFlow->getMonthlySummary($user);
		$goals = $this->goals->topForDashboard($user);

		return Inertia::render('Dashboard/Index', [
			'group' => 'dashboard',
			'netWorth' => [
				'assets' => $assets,
				'liabilities' => $liabilities,
			],
			'budgetSummary' => $budgetSummary,
			'cashFlowSummary' => $cashFlowSummary,
			'goals' => $goals,
		]);
	}
}
