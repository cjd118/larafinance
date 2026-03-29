<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TransactionImporter;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TransactionImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $transactionImporter = new TransactionImporter();
        $transactionImporter->name = 'Lloyds Bank CSV';
        $transactionImporter->class_name = 'App\TransactionImporters\LloydsBankCsvTransactionImporter';
        $transactionImporter->save();
    }

    public function testStore(): void
    {
        $response = $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'data' => $this->generateLloydsBankCsvData(),
        ]);

        $response->assertStatus(201);
    }

    public function testStoreRejectsInvalidFormat(): void
    {
        $response = $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'data' => "Wrong,Headers\nfoo,bar\n",
        ]);

        $response->assertStatus(422);
    }

    private function generateLloydsBankCsvData(): string
    {
        $csvData = "Transaction Date,Transaction Type,Sort Code,Account Number,Transaction Description,Debit Amount,Credit Amount,Balance\n";
        $csvData .= "30/01/2026,DEB,'01-02-03,12345678,mobile,8.00,,680.65\n";
        $csvData .= "26/01/2026,TFR,'01-02-03,12345678,J SMITH    24JAN26,,800.00,1318.18\n";

        return $csvData;
    }
}
