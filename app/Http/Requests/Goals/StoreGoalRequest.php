<?php

namespace App\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'name' => 'required|string',
			'target_amount' => 'required|numeric|min:0.01',
			'current_amount' => 'nullable|numeric|min:0',
			'due_date' => 'nullable|date',
		];
	}
}
