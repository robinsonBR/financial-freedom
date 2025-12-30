<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class CashAccountData extends Data
{
    public function __construct(
        public int|Optional $id,
        public int|Optional $userId,
        public ?int $institutionId,
        #[Required]
        public string $type,
        #[Required]
        public string $name,
        public string $description,
        #[Numeric]
        public float $balance,
        #[Numeric]
        public float $interestRate,
    ) {}

    public static function fromCreateRequest($request): self
    {
        return new self(
            id: Optional::create(),
            userId: $request->user()->id,
            institutionId: $request->input('institution_id'),
            type: $request->input('type'),
            name: $request->input('name'),
            description: $request->input('description', ''),
            balance: (float) ($request->input('balance') ?? 0.00),
            interestRate: (float) ($request->input('interest_rate') ?? 0.00),
        );
    }
}