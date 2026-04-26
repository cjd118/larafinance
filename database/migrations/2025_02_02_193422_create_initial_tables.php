<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        Schema::create('transaction_importers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('class_name', 255);
            $table->timestamps();
        });

        Schema::create('transaction_imports', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('fingerprint', 255);
            $table->timestamps();

            $table->foreignId('transaction_importer_id')->constrained('transaction_importers')->onDelete('cascade');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
            $table->string('description', 255);
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->foreignId('credit_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('debit_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('transaction_import_id')->nullable()->constrained('transaction_imports')->nullOnDelete();
        });

        // Double-entry invariant: credit and debit accounts must differ.
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transactions_distinct_accounts CHECK (credit_account_id <> debit_account_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
