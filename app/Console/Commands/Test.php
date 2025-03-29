<?php

namespace App\Console\Commands;

use App\Models\TransactionCategory;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach(TransactionCategory::all() as $category) {
            $this->info($category->getPath());
        }
    }
}
