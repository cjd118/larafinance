<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AccountCategory;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AccountCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private $accountCategory;

    public function setup(): void {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->accountCategory = new AccountCategory();
        $this->accountCategory->name = 'Test Category';
        $this->accountCategory->save();
    }

    public function testIndex(): void
    {
        $response = $this->get('/api/account-categories');

        $response->assertJsonFragment([
            'name' => 'Test Category',
        ]);
    }
}
