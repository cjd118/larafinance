<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeApiToken extends Command
{
    protected $signature = 'make:token {email}';

    protected $description = 'Issue a new API access token for an existing user';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $token = $user->createToken('api')->plainTextToken;

        $this->info("Access token for {$email}:");
        $this->info($token);

        return self::SUCCESS;
    }
}
