<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Sanctum\PersonalAccessToken;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $plainToken = env('SEEDED_API_TOKEN', 'localdev');

        $token = PersonalAccessToken::forceCreate([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'seeded',
            'token' => hash('sha256', $plainToken),
            'abilities' => ['*'],
        ]);

        $this->command?->info("Seeded API token for {$user->email}: {$token->id}|{$plainToken}");
    }
}
