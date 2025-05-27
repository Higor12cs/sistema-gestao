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
            $table->ulid('id')->primary()->unique();
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->unsignedBigInteger('sequential_id')->index();
            $table->foreignUlid('source_account_id')->constrained('accounts');
            $table->foreignUlid('destination_account_id')->constrained('accounts');
            $table->decimal('amount', 10, 2);
            $table->timestamp('transfer_date');
            $table->foreignUlid('debit_transaction_id')->nullable()->constrained('transactions');
            $table->foreignUlid('credit_transaction_id')->nullable()->constrained('transactions');
            $table->text('notes')->nullable();
            $table->foreignUlid('created_by')->constrained('users');
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
