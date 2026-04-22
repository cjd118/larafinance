<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|gt:0',
            //todo: add custom validation rule to ensure credit account is a credit account
            'credit_account_id' => 'required|exists:accounts,id',
            //todo: add custom validation rule to ensure debit account is a debit account
            'debit_account_id' => 'required|exists:accounts,id',
        ];
    }
}
