<?php

namespace Database\Seeders;

use App\Models\TransactionImporter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionImporterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransactionImporter::create([
            'name' => 'Lloyds Bank CSV',
            'class_name' => \App\TransactionImporters\LloydsBankCsvTransactionImporter::class,
        ]);

        // TransactionImporter::create([
        //     'name' => 'Lloyds Bank QIF',
        //     'class_name' => \App\TransactionImporters\LloydsBankQifTransactionImporter::class,
        // ]);
    }
}
