<?php

namespace App\Data\Rules;

use Spatie\LaravelData\Data;

class UpdateRuleData extends Data
{
    public function __construct(
        public string $searchString,
        public ?string $replaceString,
        public int $category_id,
    ) {}
}
