<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('name', 255);
            $table->enum('type', ['credit', 'debit']);
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->timestamps();
            $table->softDeletes();
            

            $table->foreignId('account_category_id')->constrained('account_categories')->onDelete('cascade');
        });

        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('transaction_categories')->onDelete('cascade');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('description', 255);
            $table->decimal('amount');
            $table->timestamps();

            $table->foreignId('credit_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('debit_account_id')->constrained('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
