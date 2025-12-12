<?php

namespace Tests\Feature\Goals;

use App\Models\User;
use App\Models\Goal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalsPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_goals_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/goals');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Goals/Index')
                 ->where('group', 'goals')
                 ->has('goals')
        );
    }

    /** @test */
    public function user_can_create_a_goal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/goals', [
            'name' => 'Vacation Fund',
            'target_amount' => 1000,
            'current_amount' => 250,
            'due_date' => now()->addMonths(6)->toDateString(),
        ]);

        $response->assertRedirect('/goals');

        $this->assertDatabaseHas('goals', [
            'user_id' => $user->id,
            'name' => 'Vacation Fund',
            'target_amount' => 1000,
            'current_amount' => 250,
        ]);
    }

    /** @test */
    public function user_can_update_and_delete_goal(): void
    {
        $user = User::factory()->create();

        $goal = Goal::create([
            'user_id' => $user->id,
            'name' => 'Emergency Fund',
            'target_amount' => 500,
            'current_amount' => 100,
            'due_date' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user)->put("/goals/{$goal->id}", [
            'name' => 'Emergency Fund Updated',
            'target_amount' => 800,
            'current_amount' => 200,
            'due_date' => now()->addMonths(2)->toDateString(),
        ]);

        $response->assertRedirect('/goals');

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'user_id' => $user->id,
            'name' => 'Emergency Fund Updated',
            'target_amount' => 800,
            'current_amount' => 200,
        ]);

        $deleteResponse = $this->actingAs($user)->delete("/goals/{$goal->id}");
        $deleteResponse->assertRedirect('/goals');

        $this->assertDatabaseMissing('goals', [
            'id' => $goal->id,
        ]);
    }
}
