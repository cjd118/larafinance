<?php

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function testIssuesTokenForExistingUser(): void
    {
        $user = User::factory()->create();

        $this->artisan('make:token', ['email' => $user->email])
            ->assertSuccessful();

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'api',
        ]);
    }

    public function testFailsForNonExistentUser(): void
    {
        $this->artisan('make:token', ['email' => 'nobody@example.com'])
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
