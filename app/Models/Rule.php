<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountable_id',
        'accountable_type',
        'search_string',
        'replace_string',
        'category_id',
    ];

    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
