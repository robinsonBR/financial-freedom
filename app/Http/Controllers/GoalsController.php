<?php

namespace App\Http\Controllers;

use App\Http\Requests\Goals\StoreGoalRequest;
use App\Models\Goal;
use App\Services\Goals\GoalsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoalsController extends Controller
{
	public function __construct(
		private readonly GoalsService $goals
	) {}

	public function index(Request $request): Response
	{
		$user = $request->user();
		$goals = $this->goals->listForUser($user);

		return Inertia::render('Goals/Index', [
			'group' => 'goals',
			'goals' => $goals,
		]);
	}

	public function store(StoreGoalRequest $request): RedirectResponse
	{
		$user = $request->user();

		$data = $request->validated();
		$data['user_id'] = $user->id;
		$data['current_amount'] = $data['current_amount'] ?? 0;

		Goal::create($data);

		return redirect()->route('goals.index');
	}

	public function update(StoreGoalRequest $request, Goal $goal): RedirectResponse
	{
		$user = $request->user();

		if ($goal->user_id !== $user->id) {
			abort(404);
		}

		$data = $request->validated();
		$data['current_amount'] = $data['current_amount'] ?? 0;

		$goal->update($data);

		return redirect()->route('goals.index');
	}

	public function destroy(Request $request, Goal $goal): RedirectResponse
	{
		$user = $request->user();

		if ($goal->user_id !== $user->id) {
			abort(404);
		}

		$goal->delete();

		return redirect()->route('goals.index');
	}
}

