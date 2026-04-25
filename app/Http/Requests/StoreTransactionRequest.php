<?php

namespace App\Http\Requests;

use App\Actions\CreateTransaction;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return CreateTransaction::rules();
    }
}
