<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AccountRoutingRule
 */
class AccountRoutingRuleResource extends JsonResource
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
            'matchText' => $this->match_text,
            'mode' => $this->mode,
            'sortOrder' => $this->sort_order,
            'enabled' => $this->enabled,
            'account' => new AccountResource($this->whenLoaded('account')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
