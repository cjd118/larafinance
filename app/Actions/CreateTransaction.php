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
        ];
    }

    public function execute(array $data, ?int $transactionImportId = null): Transaction
    {
        $validated = Validator::make($data, self::rules())->validate();

        $transaction = new Transaction($validated);
        $transaction->transaction_import_id = $transactionImportId;
        $transaction->save();

        return $transaction;
    }
}
