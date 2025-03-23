<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'description' => 'Salary Received',
            'amount' => 1500,
            'credit_account_id' => Account::where('name', 'Salary')->first()->id,
            'debit_account_id' => Account::where('name', 'Bank')->first()->id,
        ]);
    }
}
