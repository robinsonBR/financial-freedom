<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans';
    
    protected $fillable = [
        'user_id',
        'institution_id',
        'name',
        'type',
        'description',
        'opened_at',
        'interest_rate',
        'remaining_balance',
        'original_balance',
        'payment_amount'
    ];

    protected $casts = [
        'import_map' => 'object',
        'opened_at' => 'date',
        'interest_rate' => 'decimal:3',
        'remaining_balance' => 'decimal:2',
        'original_balance' => 'decimal:2',
        'payment_amount' => 'decimal:2',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function rules(): MorphMany
    {
        return $this->morphMany(Rule::class, 'accountable');
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn () => 'loan',
        );
    }
}