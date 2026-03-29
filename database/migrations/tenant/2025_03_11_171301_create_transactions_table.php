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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('receivable_id')->nullable()->constrained();
            $table->foreignId('payable_id')->nullable()->constrained();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->timestamp('transaction_date');
            $table->string('description')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
