<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'institution_id' => $this->institution_id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'opened_at' => $this->opened_at?->format('Y-m-d'),
            'interest_rate' => $this->interest_rate,
            'remaining_balance' => $this->remaining_balance,
            'original_balance' => $this->original_balance,
            'payment_amount' => $this->payment_amount,
            'paid_amount' => $this->original_balance - $this->remaining_balance,
            'institution' => new InstitutionResource($this->whenLoaded('institution')),
            'rules' => RuleResource::collection($this->whenLoaded('rules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
