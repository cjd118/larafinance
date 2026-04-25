<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private $testTransaction;
    private $debitAccount;
    private $creditAccount;

    public function setup(): void {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $debitCategory = AccountCategory::create(['name' => 'Test Debit Category', 'type' => 'debit']);
        $creditCategory = AccountCategory::create(['name' => 'Test Credit Category', 'type' => 'credit']);

        $this->debitAccount = Account::create([
            'name' => 'Test Account Debit',
            'account_category_id' => $debitCategory->id,
        ]);

        $this->creditAccount = Account::create([
            'name' => 'Test Account Credit',
            'account_category_id' => $creditCategory->id,
        ]);

        $this->testTransaction = Transaction::create([
            'description' => 'Salary Received',
            'amount' => 1500,
            'credit_account_id' => $this->creditAccount->id,
            'debit_account_id' => $this->debitAccount->id,
        ]);
    }

    public function testIndex(): void
    {
        $response = $this->get('/api/transactions');

        $response->assertJsonFragment([
            'description' => 'Salary Received',
            'amount' => "1500.00",
        ]);
    }

    public function testStore(): void
    {
        $response = $this->post('/api/transactions', [
            'description' => 'Salary Received',
            'amount' => 1000.01,
            'credit_account_id' => $this->creditAccount->id,
            'debit_account_id' => $this->debitAccount->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'description' => 'Salary Received',
            'amount' => "1000.01",
        ]);

        $response->assertJsonFragment([
            'description' => 'Salary Received',
            'amount' => 1000.01,
        ]);

        $response->assertJsonPath('transaction.creditAccount.name', 'Test Account Credit');
        $response->assertJsonPath('transaction.debitAccount.name', 'Test Account Debit');
    }

    public function testShow(): void
    {
        $response = $this->get('/api/transactions/' . $this->testTransaction->id);

        $response->assertJsonFragment([
            'description' => 'Salary Received',
            'amount' => "1500.00",
        ]);
    }

    //todo
    // public function testUpdate(): void
    // {
    //     $response = $this->put('/api/transaction-categories/' . $this->homeCategory->id, [
    //         'name' => 'Updated Home',
    //     ]);

    //     $this->assertDatabaseHas('transaction_categories', [
    //         'id' => $this->homeCategory->id,
    //         'name' => 'Updated Home',
    //     ]);
    // }

    public function testDestroy(): void
    {
        $response = $this->delete('/api/transactions/' . $this->testTransaction->id);

        $this->assertSoftDeleted('transactions', [
            'id' => $this->testTransaction->id,
        ]);
    }
}
