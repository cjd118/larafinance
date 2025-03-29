<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $homeCategory = TransactionCategory::create([
            'name' => 'Home',
            'parent_id' => null,
        ]);
        $utilitiesCategory = TransactionCategory::create([
            'name' => 'Utilities',
            'parent_id' => $homeCategory->id,
        ]);
        $diyCategory = TransactionCategory::create([
            'name' => 'DIY',
            'parent_id' => $homeCategory->id,
        ]);
        $internetCategory = TransactionCategory::create([
            'name' => 'Internet',
            'parent_id' => $homeCategory->id,
        ]);
        $propertyTaxCategory = TransactionCategory::create([
            'name' => 'Property Tax',
            'parent_id' => $homeCategory->id,
        ]);
        $mortgageCategory = TransactionCategory::create([
            'name' => 'Mortgage',
            'parent_id' => $homeCategory->id,
        ]);
        $insuranceCategory = TransactionCategory::create([
            'name' => 'Insurance',
            'parent_id' => $homeCategory->id,
        ]);

        $transportationCategory = TransactionCategory::create([
            'name' => 'Transportation',
            'parent_id' => null,
        ]);
        $fuelCategory = TransactionCategory::create([
            'name' => 'Fuel',
            'parent_id' => $transportationCategory->id,
        ]);
        $carInsuranceCategory = TransactionCategory::create([
            'name' => 'Car Insurance',
            'parent_id' => $transportationCategory->id,
        ]);

        $holidayCategory = TransactionCategory::create([
            'name' => 'Holiday',
            'parent_id' => null,
        ]);

        Transaction::create([
            'description' => 'Salary Received',
            'amount' => 1500,
            'credit_account_id' => Account::where('name', 'Salary')->first()->id,
            'debit_account_id' => Account::where('name', 'Bank')->first()->id,
        ]);
    }
}
