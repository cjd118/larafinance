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

    private AccountCategory $accountCategory;
    private AccountCategory $secondCreditCategory;
    private AccountCategory $debitCategory;
    private Account $account;

    public function setup(): void {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->accountCategory = AccountCategory::create(['name' => 'Test Category', 'type' => 'credit']);
        $this->secondCreditCategory = AccountCategory::create(['name' => 'Other Credit Category', 'type' => 'credit']);
        $this->debitCategory = AccountCategory::create(['name' => 'Debit Category', 'type' => 'debit']);

        $this->account = Account::create([
            'name' => 'Test Account',
            'account_category_id' => $this->accountCategory->id,
        ]);
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
        $response = $this->postJson('/api/accounts', [
            'name' => 'New Account',
            'account_category_id' => $this->accountCategory->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('accounts', [
            'name' => 'New Account',
            'account_category_id' => $this->accountCategory->id,
        ]);
    }

    public function testStoreRejectsDuplicateNameWithinSameCategoryType(): void
    {
        $response = $this->postJson('/api/accounts', [
            'name' => 'Test Account',
            'account_category_id' => $this->secondCreditCategory->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function testStoreAllowsDuplicateNameAcrossDifferentCategoryTypes(): void
    {
        $response = $this->postJson('/api/accounts', [
            'name' => 'Test Account',
            'account_category_id' => $this->debitCategory->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('accounts', [
            'name' => 'Test Account',
            'account_category_id' => $this->debitCategory->id,
        ]);
    }

    public function testStoreIgnoresSoftDeletedAccountsWhenCheckingUniqueness(): void
    {
        $this->account->delete();

        $response = $this->postJson('/api/accounts', [
            'name' => 'Test Account',
            'account_category_id' => $this->accountCategory->id,
        ]);

        $response->assertStatus(201);
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

    public function testUpdateRejectsRenameToConflictingNameWithinSameCategoryType(): void
    {
        Account::create([
            'name' => 'Conflicting Account',
            'account_category_id' => $this->secondCreditCategory->id,
        ]);

        $response = $this->putJson('/api/accounts/' . $this->account->id, [
            'name' => 'Conflicting Account',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function testUpdateAllowsKeepingTheSameName(): void
    {
        $response = $this->putJson('/api/accounts/' . $this->account->id, [
            'name' => 'Test Account',
        ]);

        $response->assertStatus(200);
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
