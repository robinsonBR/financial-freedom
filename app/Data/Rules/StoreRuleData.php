<?php

namespace App\Data\Rules;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class StoreRuleData extends Data
{
    public function __construct(
        #[Required]
        public array $account,
        #[Required]
        public string $searchString,
        #[Required]
        public string $replaceString,
        #[Required]
        public array $category,
    ) {}

    public function getAccountId(): int
    {
        return $this->account['id'];
    }

    public function getAccountType(): string
    {
        return $this->account['type'];
    }

    public function getCategoryId(): int
    {
        return $this->category['id'];
    }
}