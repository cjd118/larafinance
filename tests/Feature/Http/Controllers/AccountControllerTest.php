<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\Transaction;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    private $accountCategory;
    private $account;

    public function setup(): void {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->accountCategory = new AccountCategory();
        $this->accountCategory->name = 'Test Category';
        $this->accountCategory->save();

        $this->account = new Account();
        $this->account->name = 'Test Account';
        $this->account->account_category_id = $this->accountCategory->id;
        $this->account->save();
    }

    public function testIndex(): void
    {
        $response = $this->get('/api/accounts');

        $response->assertJsonFragment([
            'name' => 'Test Account',
        ]);
    }

    public function testStore(): void
    {
        $response = $this->post('/api/accounts', [
            'name' => 'Test Account',
            'account_category_id' => $this->accountCategory->id,
        ]);

        $this->assertDatabaseHas('accounts', [
            'name' => 'Test Account',
            'account_category_id' => $this->accountCategory->id,
        ]);
    }

    public function testShow(): void
    {
        $response = $this->get('/api/accounts/' . $this->account->id);

        $response->assertJsonFragment([
            'name' => 'Test Account',
        ]);
    }

    public function testUpdate(): void
    {
        $response = $this->put('/api/accounts/' . $this->account->id, [
            'name' => 'Updated Account',
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $this->account->id,
            'name' => 'Updated Account',
        ]);

        $response->assertJsonFragment([
            'name' => 'Updated Account',
            'accountCategory' => [
                'id' => $this->accountCategory->id,
                'name' => 'Test Category',
                'type' => 'credit',
                'createdAt' => $this->accountCategory->created_at,
                'updatedAt' => $this->accountCategory->updated_at,
            ],
        ]);
    }

    public function testDestroy(): void
    {
        $response = $this->delete('/api/accounts/' . $this->account->id);

        $this->assertSoftDeleted('accounts', [
            'id' => $this->account->id,
        ]);
    }

    public function testForceDeletingAccountWithTransactionsFails(): void
    {
        $otherAccount = new Account();
        $otherAccount->name = 'Other Account';
        $otherAccount->account_category_id = $this->accountCategory->id;
        $otherAccount->save();

        Transaction::create([
            'description' => 'pin the FK',
            'amount' => 1000,
            'credit_account_id' => $this->account->id,
            'debit_account_id' => $otherAccount->id,
        ]);

        $this->expectException(QueryException::class);
        $this->account->forceDelete();
    }

    public function testForceDeletingAccountCategoryWithAccountsFails(): void
    {
        $this->expectException(QueryException::class);
        $this->accountCategory->forceDelete();
    }
}
