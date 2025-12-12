<?php

namespace Tests\Feature\Budget;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_budget_plan_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/budget');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Budget/Index')
                 ->where('group', 'budget-plan')
                 ->has('year')
                 ->has('month')
                 ->has('categories')
                 ->has('transactions')
        );
    }
}
