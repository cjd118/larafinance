<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Account;
use App\Models\AccountCategory;
use App\Models\AccountRoutingRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountRoutingRuleControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $expenseAccount;
    private Account $otherExpenseAccount;

    public function setup(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create(), ['*']);

        $expensesCategory = AccountCategory::create(['name' => 'Expenses', 'type' => 'debit']);

        $this->expenseAccount = Account::create([
            'name' => 'Utilities',
            'account_category_id' => $expensesCategory->id,
        ]);

        $this->otherExpenseAccount = Account::create([
            'name' => 'Groceries',
            'account_category_id' => $expensesCategory->id,
        ]);
    }

    public function testIndex(): void
    {
        AccountRoutingRule::create([
            'match_text' => 'TESCO',
            'mode' => 'contains',
            'account_id' => $this->expenseAccount->id,
            'sort_order' => 5,
        ]);
        AccountRoutingRule::create([
            'match_text' => 'WATER',
            'mode' => 'exact',
            'account_id' => $this->expenseAccount->id,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/account-routing-rules');

        $response->assertStatus(200);
        // Lower sort_order comes first
        $body = $response->json();
        $this->assertEquals('WATER', $body['data'][0]['matchText']);
        $this->assertEquals('TESCO', $body['data'][1]['matchText']);
    }

    public function testStore(): void
    {
        $response = $this->postJson('/api/account-routing-rules', [
            'match_text' => 'SOUTH WEST WATER',
            'mode' => 'exact',
            'account_id' => $this->expenseAccount->id,
            'sort_order' => 10,
            'enabled' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('account_routing_rules', [
            'match_text' => 'SOUTH WEST WATER',
            'mode' => 'exact',
            'account_id' => $this->expenseAccount->id,
            'sort_order' => 10,
            'enabled' => true,
        ]);
    }

    public function testStoreRejectsInvalidMode(): void
    {
        $response = $this->postJson('/api/account-routing-rules', [
            'match_text' => 'TESCO',
            'mode' => 'regex',
            'account_id' => $this->expenseAccount->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('mode');
    }

    public function testStoreRejectsNonExistentAccount(): void
    {
        $response = $this->postJson('/api/account-routing-rules', [
            'match_text' => 'TESCO',
            'mode' => 'contains',
            'account_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    public function testStoreRequiresFields(): void
    {
        $response = $this->postJson('/api/account-routing-rules', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['match_text', 'mode', 'account_id']);
    }

    public function testShow(): void
    {
        $rule = AccountRoutingRule::create([
            'match_text' => 'TESCO',
            'mode' => 'contains',
            'account_id' => $this->expenseAccount->id,
        ]);

        $response = $this->getJson('/api/account-routing-rules/' . $rule->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(['matchText' => 'TESCO']);
    }

    public function testUpdate(): void
    {
        $rule = AccountRoutingRule::create([
            'match_text' => 'TESCO',
            'mode' => 'contains',
            'account_id' => $this->expenseAccount->id,
        ]);

        $response = $this->putJson('/api/account-routing-rules/' . $rule->id, [
            'match_text' => 'TESCO STORES',
            'account_id' => $this->otherExpenseAccount->id,
            'enabled' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('account_routing_rules', [
            'id' => $rule->id,
            'match_text' => 'TESCO STORES',
            'account_id' => $this->otherExpenseAccount->id,
            'enabled' => false,
        ]);
    }

    public function testDestroy(): void
    {
        $rule = AccountRoutingRule::create([
            'match_text' => 'TESCO',
            'mode' => 'contains',
            'account_id' => $this->expenseAccount->id,
        ]);

        $response = $this->deleteJson('/api/account-routing-rules/' . $rule->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('account_routing_rules', ['id' => $rule->id]);
    }

}
