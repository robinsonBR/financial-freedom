<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    
    protected $fillable = [
        'user_id',
        'group_id',
        'name',
        'color',
        'monthly_budget',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}