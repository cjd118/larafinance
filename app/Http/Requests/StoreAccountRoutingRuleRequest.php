<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRoutingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'match_text' => 'required|string|max:255',
            'mode' => 'required|in:contains,exact',
            'account_id' => 'required|exists:accounts,id,deleted_at,NULL',
            'sort_order' => 'sometimes|integer|min:0',
            'enabled' => 'sometimes|boolean',
        ];
    }
}
