<?php

namespace Tests\Feature\Budget;

use App\Models\Category;
use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryBudgetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_category_with_monthly_budget_and_see_it_on_budget_page(): void
    {
        $user = User::factory()->create();

        $group = Group::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/settings/categories', [
            'name' => 'Groceries',
            'color' => 'green',
            'group_id' => $group->id,
            'monthly_budget' => 500,
        ]);

        $category = Category::first();
        $this->assertNotNull($category);
        $this->assertEquals(500.0, $category->monthly_budget);

        $response = $this->actingAs($user)->get('/budget');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Budget/Index')
                ->has('categories')
        );
    }
}
