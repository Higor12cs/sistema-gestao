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
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_account_id')->constrained('accounts');
            $table->foreignId('destination_account_id')->constrained('accounts');
            $table->decimal('amount', 10, 2);
            $table->timestamp('transfer_date');
            $table->foreignId('debit_transaction_id')->nullable()->constrained('transactions');
            $table->foreignId('credit_transaction_id')->nullable()->constrained('transactions');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
    }
};
