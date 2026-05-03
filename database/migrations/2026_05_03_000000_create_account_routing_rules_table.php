<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_routing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('match_text', 255);
            $table->enum('mode', ['contains', 'exact'])->default('contains');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
    }
};
