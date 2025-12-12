<?php

namespace App\Http\Controllers;

use App\Services\CashFlow\CashFlowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashFlowController extends Controller
{
	public function __construct(
		private readonly CashFlowService $cashFlow,
	) {}

	public function index(Request $request): Response
	{
		$user = $request->user();

		$data = $this->cashFlow->getMonthlyCashFlowPageData($user);

		return Inertia::render('CashFlow/Index', [
			'group' => 'cash-flow',
			'year' => $data['year'],
			'month' => $data['month'],
			'summary' => $data['summary'],
			'categorySummary' => $data['categorySummary'],
			'transactions' => $data['transactions'],
		]);
	}
}

