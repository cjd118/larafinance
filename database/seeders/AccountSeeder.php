<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assetsCategory = AccountCategory::create(['name' => 'Assets', 'type' => 'debit']);
        $liabilitiesCategory = AccountCategory::create(['name' => 'Liabilities', 'type' => 'credit']);
        $incomeCategory = AccountCategory::create(['name' => 'Income', 'type' => 'credit']);
        $expensesCategory = AccountCategory::create(['name' => 'Expenses', 'type' => 'debit']);

        Account::create([
            'name' => 'Bank',
            'account_category_id' => $assetsCategory->id,
        ]);

        Account::create([
            'name' => 'Mortgage',
            'account_category_id' => $liabilitiesCategory->id,
        ]);

        Account::create([
            'name' => 'Salary',
            'account_category_id' => $incomeCategory->id,
        ]);

        Account::create([
            'name' => 'Utilities',
            'account_category_id' => $expensesCategory->id,
        ]);

        Account::create([
            'name' => 'Unassigned Income',
            'account_category_id' => $incomeCategory->id,
        ]);

        Account::create([
            'name' => 'Unassigned Expense',
            'account_category_id' => $expensesCategory->id,
        ]);
    }
}
