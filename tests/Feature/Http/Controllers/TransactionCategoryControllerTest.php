<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TransactionCategory;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TransactionCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private $homeCategory;

    public function setup(): void {
        parent::setUp();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->homeCategory = TransactionCategory::create([
            'name' => 'Home',
            'parent_id' => null,
        ]);

        $utilitiesCategory = TransactionCategory::create([
            'name' => 'Utilities',
            'parent_id' => $this->homeCategory->id,
        ]);
        $diyCategory = TransactionCategory::create([
            'name' => 'DIY',
            'parent_id' => $this->homeCategory->id,
        ]);
        $internetCategory = TransactionCategory::create([
            'name' => 'Internet',
            'parent_id' => $this->homeCategory->id,
        ]);
        $propertyTaxCategory = TransactionCategory::create([
            'name' => 'Property Tax',
            'parent_id' => $this->homeCategory->id,
        ]);
        $mortgageCategory = TransactionCategory::create([
            'name' => 'Mortgage',
            'parent_id' => $this->homeCategory->id,
        ]);
        $insuranceCategory = TransactionCategory::create([
            'name' => 'Insurance',
            'parent_id' => $this->homeCategory->id,
        ]);

        $transportationCategory = TransactionCategory::create([
            'name' => 'Transportation',
            'parent_id' => null,
        ]);
        $fuelCategory = TransactionCategory::create([
            'name' => 'Fuel',
            'parent_id' => $transportationCategory->id,
        ]);
        $carInsuranceCategory = TransactionCategory::create([
            'name' => 'Car Insurance',
            'parent_id' => $transportationCategory->id,
        ]);
    }

    public function testIndex(): void
    {
        $response = $this->get('/api/transaction-categories');

        $responseDecoded = $response->decodeResponseJson();

        $this->assertEquals('Home', $responseDecoded[0]['name']);
        $this->assertEquals('Home', $responseDecoded[0]['path']);
        $this->assertEquals('DIY', $responseDecoded[1]['name']);
        $this->assertEquals('Home > DIY', $responseDecoded[1]['path']);
    }

    public function testStore(): void
    {
        $response = $this->post('/api/transaction-categories', [
            'name' => 'Test Category',
            'parent_id' => null,
        ]);

        $this->assertDatabaseHas('transaction_categories', [
            'name' => 'Test Category',
            'parent_id' => null,
        ]);
    }

    public function testShow(): void
    {
        $response = $this->get('/api/transaction-categories/' . $this->homeCategory->id);

        $response->assertJsonFragment([
            'name' => 'Home',
            'parentId' => null,
        ]);
    }

    public function testUpdate(): void
    {
        $response = $this->put('/api/transaction-categories/' . $this->homeCategory->id, [
            'name' => 'Updated Home',
        ]);

        $this->assertDatabaseHas('transaction_categories', [
            'id' => $this->homeCategory->id,
            'name' => 'Updated Home',
        ]);
    }

    public function testDestroy(): void
    {
        $response = $this->delete('/api/transaction-categories/' . $this->homeCategory->id);

        $this->assertSoftDeleted('transaction_categories', [
            'id' => $this->homeCategory->id,
        ]);
    }
}
