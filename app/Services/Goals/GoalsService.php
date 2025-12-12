<?php

namespace App\Services\Goals;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Support\Collection;

class GoalsService
{
	public function listForUser(User $user): Collection
	{
		return Goal::query()
			->where('user_id', $user->id)
			->orderBy('name')
			->get(['id', 'name', 'target_amount', 'current_amount', 'due_date'])
			->map(fn (Goal $goal) => $this->transformGoal($goal));
	}

	public function topForDashboard(User $user, int $limit = 3): Collection
	{
		return Goal::query()
			->where('user_id', $user->id)
			->orderBy('due_date')
			->orderBy('name')
			->limit($limit)
			->get(['id', 'name', 'target_amount', 'current_amount', 'due_date'])
			->map(fn (Goal $goal) => $this->transformGoal($goal));
	}

	private function transformGoal(Goal $goal): array
	{
		$target = (float) $goal->target_amount;
		$current = (float) $goal->current_amount;
		$progress = $target > 0 ? min(1.0, max(0.0, $current / $target)) : 0.0;

		return [
			'id' => $goal->id,
			'name' => $goal->name,
			'target_amount' => $target,
			'current_amount' => $current,
			'due_date' => optional($goal->due_date)->toDateString(),
			'progress' => $progress,
		];
	}
}
