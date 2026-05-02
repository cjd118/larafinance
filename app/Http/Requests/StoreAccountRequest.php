<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Account::uniqueNameRule(accountCategoryId: $this->integer('account_category_id')),
            ],
            'account_category_id' => 'required|exists:account_categories,id',
        ];
    }
}
