<?php

namespace App\Actions;

use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class CreateTransaction
{
    public static function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|integer|gt:0',
            'credit_account_id' => ['required', 'exists:accounts,id'],
            'debit_account_id' => ['required', 'exists:accounts,id', 'different:credit_account_id'],
            'transaction_import_id' => ['nullable', 'exists:transaction_imports,id'],
        ];
    }

    public function execute(array $data): Transaction
    {
        $validated = Validator::make($data, self::rules())->validate();

        return Transaction::create($validated);
    }
}
