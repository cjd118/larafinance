<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountRoutingRule;
use Illuminate\Database\Seeder;

class AccountRoutingRuleSeeder extends Seeder
{
    public function run(): void
    {
        $utilities = Account::where('name', 'Utilities')->value('id');
        $mortgage = Account::where('name', 'Mortgage')->value('id');

        AccountRoutingRule::create([
            'match_text' => 'SOUTH WEST WATER',
            'mode' => 'exact',
            'account_id' => $utilities,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'E.ON NEXT LTD',
            'mode' => 'exact',
            'account_id' => $utilities,
        ]);

        AccountRoutingRule::create([
            'match_text' => 'NATIONWIDE B S',
            'mode' => 'exact',
            'account_id' => $mortgage,
        ]);
    }
}
