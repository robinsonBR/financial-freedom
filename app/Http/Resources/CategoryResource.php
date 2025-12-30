<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'group_id' => $this->group_id,
            'name' => $this->name,
            'color' => $this->color,
            'monthly_budget' => $this->monthly_budget,
            'group' => new GroupResource($this->whenLoaded('group')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
