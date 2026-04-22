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
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:transaction_categories,id',
                function ($attribute, $value, $fail) use ($id) {
                    $currentPath = TransactionCategory::find($value)->getPath();
                    foreach ($currentPath as $pathElement) {
                        if ($pathElement->id == $id) {
                            return $fail('Parent cannot be child of itself');
                        }
                    }
                },
            ],
        ];
    }
}
