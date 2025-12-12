<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function authenticated_user_can_view_dashboard_with_summaries(): void
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)->get('/');

		$response->assertStatus(200);
		$response->assertInertia(fn ($page) =>
			$page->component('Dashboard/Index')
				->where('group', 'dashboard')
				->has('netWorth')
				->has('budgetSummary')
				->has('cashFlowSummary')
				->has('goals')
		);
	}
}
