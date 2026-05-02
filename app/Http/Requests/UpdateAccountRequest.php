<?php

namespace App\Http\Requests;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $account = Account::find($this->route('account'));
        $categoryId = $this->integer('account_category_id') ?: $account?->account_category_id;

        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:255',
                Account::uniqueNameRule(
                    accountCategoryId: $categoryId,
                    ignoreId: (int) $this->route('account'),
                ),
            ],
            'account_category_id' => 'sometimes|required|exists:account_categories,id',
        ];
    }
}
