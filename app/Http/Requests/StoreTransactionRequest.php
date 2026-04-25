<?php

namespace App\Http\Requests;

use App\Validators\AccountMustBeOfType;
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
            'credit_account_id' => ['required', 'exists:accounts,id', new AccountMustBeOfType('credit')],
            'debit_account_id' => ['required', 'exists:accounts,id', new AccountMustBeOfType('debit')],
        ];
    }
}
