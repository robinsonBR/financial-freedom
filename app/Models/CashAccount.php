<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CashAccount extends Model
{
    use HasFactory;

    protected $table = 'cash_accounts';
    
    protected $fillable = [
        'user_id',
        'institution_id',
        'type',
        'name',
        'description',
        'account_number',
        'balance',
        'interest_rate'
    ];

    protected $casts = [
        'import_map' => 'object',
        'balance' => 'decimal:2',
        'interest_rate' => 'decimal:3',
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
            get: fn () => 'cash-account',
        );
    }
}