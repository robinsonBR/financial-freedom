<?php

namespace Database\Factories;

use App\Models\CashAccount;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashAccount>
 */
class CashAccountFactory extends Factory
{
	protected $model = CashAccount::class;

	public function definition(): array
	{
		return [
			'user_id' => User::factory(),
			'institution_id' => Institution::factory(),
			'type' => 'checking',
			'name' => $this->faker->word . ' Account',
			'description' => $this->faker->sentence,
			'account_number' => (string) $this->faker->randomNumber(8, true),
			'balance' => 0,
			'interest_rate' => 0,
		];
	}
}
