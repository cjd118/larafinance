<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRoutingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'match_text' => 'sometimes|required|string|max:255',
            'mode' => 'sometimes|required|in:contains,exact',
            'account_id' => 'sometimes|required|exists:accounts,id',
            'sort_order' => 'sometimes|integer|min:0',
            'enabled' => 'sometimes|boolean',
        ];
    }
}
