<?php

namespace App\Http\Requests;

use App\Models\TransactionCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('transaction_category');

        return [
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => [
                'sometimes',
                'bail',
                'nullable',
                'exists:transaction_categories,id',
                function ($attribute, $value, $fail) use ($id) {
                    $currentPath = TransactionCategory::find($value)->getPath();
                    if (array_any($currentPath, fn ($p) => $p->id == $id)) {
                        $fail('Parent cannot be child of itself');
                    }
                },
            ],
        ];
    }
}
