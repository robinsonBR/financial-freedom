<?php

namespace Modules\Transaction\Models;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Database\Factories\TransactionFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'accountable_id',
        'accountable_type',
        'category_id',
        'amount',
        'date',
        'merchant',
        'notes',
        'type',
        'reconciled',
        'receipt_url',
        'original',
        'plaid_transaction_id',
        'merchant_name',
        'pending',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'reconciled' => 'boolean',
        'pending' => 'boolean',
    ];

    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): TransactionFactory
    {
        return TransactionFactory::new();
    }
}
