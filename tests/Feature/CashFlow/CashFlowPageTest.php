<?php

namespace Tests\Feature\CashFlow;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Transaction\Models\Transaction;
use Tests\TestCase;

class CashFlowPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_cash_flow_page_and_summary(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 100,
            'type' => 'credit',
            'date' => now()->format('Y-m-d'),
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 40,
            'type' => 'debit',
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get('/cash-flow');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('CashFlow/Index')
                 ->where('group', 'cash-flow')
                 ->has('year')
                 ->has('month')
                 ->has('summary')
                 ->has('categorySummary')
                 ->has('transactions')
        );
    }
}
