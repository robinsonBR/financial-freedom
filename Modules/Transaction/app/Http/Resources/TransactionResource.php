<?php

namespace Modules\Transaction\Http\Resources;

use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'accountable_id' => $this->accountable_id,
            'accountable_type' => $this->accountable_type,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'merchant' => $this->merchant,
            'notes' => $this->notes,
            'type' => $this->type,
            'reconciled' => $this->reconciled,
            'receipt_url' => $this->receipt_url,
            'original' => $this->original,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'accountable' => $this->whenLoaded('accountable'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
