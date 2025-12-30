<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditCardResource extends JsonResource
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
            'brand' => $this->brand,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'interest_rate' => $this->interest_rate,
            'credit_limit' => $this->credit_limit,
            'balance' => $this->balance,
            'available_credit' => $this->credit_limit - $this->balance,
            'institution' => new InstitutionResource($this->whenLoaded('institution')),
            'rules' => RuleResource::collection($this->whenLoaded('rules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
