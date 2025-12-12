<?php

namespace App\Http\Controllers;

use App\Services\Budget\BudgetService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
	public function __construct(
		private readonly BudgetService $budget,
	) {}

	public function index(Request $request): Response
	{
		$user = $request->user();

		$data = $this->budget->getMonthlyBudgetPageData($user);

		return Inertia::render('Budget/Index', [
			'group' => 'budget-plan',
			'year' => $data['year'],
			'month' => $data['month'],
			'categories' => $data['categories'],
			'transactions' => $data['transactions'],
		]);
	}
}

