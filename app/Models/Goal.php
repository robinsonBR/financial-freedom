<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'name',
		'target_amount',
		'current_amount',
		'due_date',
	];

	protected $casts = [
		'target_amount' => 'decimal:2',
		'current_amount' => 'decimal:2',
		'due_date' => 'date',
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
