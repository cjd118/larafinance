<?php

namespace App\Validators;

use App\Models\Account;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AccountMustBeOfType implements ValidationRule
{
    public function __construct(private string $type) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $account = Account::with('category')->find($value);

        if ($account === null || $account->category === null) {
            return;
        }

        if ($account->category->type !== $this->type) {
            $fail("The :attribute must reference an account whose category is of type {$this->type}.");
        }
    }
}
