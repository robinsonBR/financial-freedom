<?php

namespace Tests\Feature\Transactions;

use App\Models\User;
use App\Models\CashAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Transaction\Models\Transaction;
use Tests\TestCase;

class ImportTransactionsTest extends TestCase
{
	use RefreshDatabase;

	public function test_it_imports_transactions_for_a_cash_account(): void
	{
		$this->actingAs(User::factory()->create());

		$account = CashAccount::factory()->create();

		$payload = [
			'account' => [
				'id' => $account->id,
				'type' => 'cash-account',
			],
			'transactions' => [
				[
					'category' => null,
					'amount' => 10000,
					'date' => '2024-01-15',
					'name' => 'Test Merchant',
					'notes' => 'Imported',
					'direction' => 'debit',
				],
			],
		];

		$response = $this->post('/transactions/import', $payload);

		$response->assertRedirect('/transactions');

		$this->assertDatabaseCount(Transaction::class, 1);

		$transaction = Transaction::first();

		$this->assertSame($account->id, $transaction->accountable_id);
		$this->assertSame(CashAccount::class, $transaction->accountable_type);
		$this->assertNull($transaction->category_id);
		$this->assertSame(10000, $transaction->amount);
		$this->assertSame('2024-01-15', (string) $transaction->date);
	}
}
