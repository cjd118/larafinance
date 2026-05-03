<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\AccountRoutingRule;
use App\Models\Transaction;
use App\Models\TransactionImporter;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TransactionImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $bankAccount;
    private AccountCategory $incomeCategory;
    private AccountCategory $expensesCategory;

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
        $this->incomeCategory = AccountCategory::create(['name' => 'Income', 'type' => 'credit']);
        $this->expensesCategory = AccountCategory::create(['name' => 'Expenses', 'type' => 'debit']);

        $this->bankAccount = Account::create([
            'name' => 'Bank',
            'account_category_id' => $assetsCategory->id,
        ]);

        Account::create([
            'name' => 'Unassigned Income',
            'account_category_id' => $this->incomeCategory->id,
        ]);

        Account::create([
            'name' => 'Unassigned Expense',
            'account_category_id' => $this->expensesCategory->id,
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

    public function testStoreAppliesMatchingDebitRule(): void
    {
        $mobileBills = Account::create([
            'name' => 'Mobile Bills',
            'account_category_id' => $this->expensesCategory->id,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'mobile',
            'mode' => 'contains',
            'account_id' => $mobileBills->id,
        ]);

        $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $this->generateLloydsBankCsvData()),
        ])->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'description' => 'mobile',
            'amount' => 800,
            'credit_account_id' => $this->bankAccount->id,
            'debit_account_id' => $mobileBills->id,
        ]);
    }

    public function testStoreAppliesMatchingCreditRule(): void
    {
        $salary = Account::create([
            'name' => 'Salary',
            'account_category_id' => $this->incomeCategory->id,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'J SMITH',
            'mode' => 'contains',
            'account_id' => $salary->id,
        ]);

        $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $this->generateLloydsBankCsvData()),
        ])->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'description' => 'J SMITH    24JAN26',
            'amount' => 80000,
            'debit_account_id' => $this->bankAccount->id,
            'credit_account_id' => $salary->id,
        ]);
    }

    public function testStoreSkipsRuleWhenAccountTypeMismatchesTransactionSide(): void
    {
        // Rule points at a debit-typed account but the matching row's counterpart
        // is on the credit side — should fall through to Unassigned Income.
        $mobileBills = Account::create([
            'name' => 'Mobile Bills',
            'account_category_id' => $this->expensesCategory->id,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'J SMITH',
            'mode' => 'contains',
            'account_id' => $mobileBills->id,
        ]);

        $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $this->generateLloydsBankCsvData()),
        ])->assertStatus(201);

        $unassignedIncomeId = Account::where('name', 'Unassigned Income')->value('id');
        $this->assertDatabaseHas('transactions', [
            'description' => 'J SMITH    24JAN26',
            'credit_account_id' => $unassignedIncomeId,
        ]);
    }

    public function testStoreUsesHighestPriorityRuleWhenMultipleMatch(): void
    {
        $mobileBills = Account::create([
            'name' => 'Mobile Bills',
            'account_category_id' => $this->expensesCategory->id,
        ]);
        $genericExpense = Account::create([
            'name' => 'Generic Expense',
            'account_category_id' => $this->expensesCategory->id,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'mobile',
            'mode' => 'contains',
            'account_id' => $genericExpense->id,
            'sort_order' => 10,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'mobile',
            'mode' => 'contains',
            'account_id' => $mobileBills->id,
            'sort_order' => 1,
        ]);

        $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $this->generateLloydsBankCsvData()),
        ])->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'description' => 'mobile',
            'debit_account_id' => $mobileBills->id,
        ]);
    }

    public function testStoreIgnoresDisabledRule(): void
    {
        $mobileBills = Account::create([
            'name' => 'Mobile Bills',
            'account_category_id' => $this->expensesCategory->id,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'mobile',
            'mode' => 'contains',
            'account_id' => $mobileBills->id,
            'enabled' => false,
        ]);

        $this->post('/api/transaction-imports', [
            'name' => 'Lloyds Bank CSV',
            'account_id' => $this->bankAccount->id,
            'file' => UploadedFile::fake()->createWithContent('statement.csv', $this->generateLloydsBankCsvData()),
        ])->assertStatus(201);

        $unassignedExpenseId = Account::where('name', 'Unassigned Expense')->value('id');
        $this->assertDatabaseHas('transactions', [
            'description' => 'mobile',
            'debit_account_id' => $unassignedExpenseId,
        ]);
    }

    private function generateLloydsBankCsvData(): string
    {
        $csvData = "Transaction Date,Transaction Type,Sort Code,Account Number,Transaction Description,Debit Amount,Credit Amount,Balance\n";
        $csvData .= "30/01/2026,DEB,'01-02-03,12345678,mobile,8.00,,680.65\n";
        $csvData .= "26/01/2026,TFR,'01-02-03,12345678,J SMITH    24JAN26,,800.00,1318.18\n";

        return $csvData;
    }
}
