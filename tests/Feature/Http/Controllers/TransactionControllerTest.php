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

    public function setup(): void {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $accountCategory = new AccountCategory();
        $accountCategory->name = 'Test Category';
        $accountCategory->save();

        $account1 = new Account();
        $account1->name = 'Test Account Debit';
        $account1->account_category_id = $accountCategory->id;
        $account1->save();

        $account2 = new Account();
        $account2->name = 'Test Account Credit';
        $account2->account_category_id = $accountCategory->id;
        $account2->save();

        $this->testTransaction = Transaction::create([
            'description' => 'Salary Received',
            'amount' => 1500,
            'credit_account_id' => $account2->id,
            'debit_account_id' => $account1->id,
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

    // public function testStore(): void
    // {
    //     $response = $this->post('/api/transaction-categories', [
    //         'name' => 'Test Category',
    //         'parent_id' => null,
    //     ]);

    //     $this->assertDatabaseHas('transaction_categories', [
    //         'name' => 'Test Category',
    //         'parent_id' => null,
    //     ]);
    // }

    // public function testShow(): void
    // {
    //     $response = $this->get('/api/transaction-categories/' . $this->homeCategory->id);

    //     $response->assertJsonFragment([
    //         'name' => 'Home',
    //         'parentId' => null,
    //     ]);
    // }

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

    // public function testDestroy(): void
    // {
    //     $response = $this->delete('/api/transaction-categories/' . $this->homeCategory->id);

    //     $this->assertSoftDeleted('transaction_categories', [
    //         'id' => $this->homeCategory->id,
    //     ]);
    // }
}
