<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|exists:transaction_importers,name',
            'account_id' => 'required|integer|exists:accounts,id',
            'data' => 'required',
        ];
    }
}
