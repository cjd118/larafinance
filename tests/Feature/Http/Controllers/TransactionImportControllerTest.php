<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\Transaction;
use App\Models\TransactionImporter;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TransactionImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $bankAccount;

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

        $assetsCategory = AccountCategory::create(['name' => 'Assets', 'type' => 'debit']);
        $incomeCategory = AccountCategory::create(['name' => 'Income', 'type' => 'credit']);
        $expensesCategory = AccountCategory::create(['name' => 'Expenses', 'type' => 'debit']);

        $this->bankAccount = Account::create([
            'name' => 'Bank',
            'account_category_id' => $assetsCategory->id,
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

    public function testStore(): void
    {
        $response = $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $this->generateLloydsBankCsvData()),
        ]);

        $response->assertStatus(201);
        $response->assertJson(['imported_count' => 2]);

        $this->assertDatabaseCount('transactions', 2);

        $unassignedIncome = Account::where('name', 'Unassigned Income')->first();
        $unassignedExpense = Account::where('name', 'Unassigned Expense')->first();

        $this->assertDatabaseHas('transactions', [
            'description' => 'mobile',
            'amount' => 800,
            'credit_account_id' => $this->bankAccount->id,
            'debit_account_id' => $unassignedExpense->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'description' => 'J SMITH    24JAN26',
            'amount' => 80000,
            'debit_account_id' => $this->bankAccount->id,
            'credit_account_id' => $unassignedIncome->id,
        ]);
    }

    public function testStoreRejectsDuplicateImport(): void
    {
        $csvData = $this->generateLloydsBankCsvData();

        $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $csvData),
        ])->assertStatus(201);

        $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $csvData),
        ])->assertStatus(409);
    }

    public function testStoreRejectsInvalidFormat(): void
    {
        $response = $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', "Wrong,Headers\nfoo,bar\n"),
        ]);

        $response->assertStatus(422);
    }

    public function testStoreRejectsRowWithMalformedDate(): void
    {
        $csv = "Transaction Date,Transaction Type,Sort Code,Account Number,Transaction Description,Debit Amount,Credit Amount,Balance\n";
        $csv .= "not-a-date,DEB,'01-02-03,12345678,mobile,8.00,,680.65\n";

        $response = $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $csv),
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function testStoreRejectsRowWithMalformedAmount(): void
    {
        $csv = "Transaction Date,Transaction Type,Sort Code,Account Number,Transaction Description,Debit Amount,Credit Amount,Balance\n";
        $csv .= "30/01/2026,DEB,'01-02-03,12345678,mobile,abc,,680.65\n";

        $response = $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $csv),
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('transactions', 0);
    }

    private function generateLloydsBankCsvData(): string
    {
        $csvData = "Transaction Date,Transaction Type,Sort Code,Account Number,Transaction Description,Debit Amount,Credit Amount,Balance\n";
        $csvData .= "30/01/2026,DEB,'01-02-03,12345678,mobile,8.00,,680.65\n";
        $csvData .= "26/01/2026,TFR,'01-02-03,12345678,J SMITH    24JAN26,,800.00,1318.18\n";

        return $csvData;
    }
}
