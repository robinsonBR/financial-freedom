<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
	protected $model = Institution::class;

	public function definition(): array
	{
		return [
			'name' => $this->faker->company,
			'url' => $this->faker->url,
			'logo' => null,
		];
	}
}
