<?php

namespace Database\Factories;

use App\Models\CashAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Transaction\Models\Transaction;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $cashAccount = CashAccount::factory();

        return [
            'uuid' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'accountable_id' => $cashAccount,
            'accountable_type' => CashAccount::class,
            'category_id' => null,
            'amount' => $this->faker->randomFloat(2, 1, 500),
            'date' => $this->faker->date('Y-m-d'),
            'merchant' => $this->faker->company(),
            'type' => 'debit',
            'reconciled' => false,
            'receipt_url' => null,
            'original' => null,
        ];
    }
}
