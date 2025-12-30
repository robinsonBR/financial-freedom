<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashAccountResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'account_number' => $this->account_number,
            'balance' => $this->balance,
            'interest_rate' => $this->interest_rate,
            'institution' => new InstitutionResource($this->whenLoaded('institution')),
            'rules' => RuleResource::collection($this->whenLoaded('rules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
