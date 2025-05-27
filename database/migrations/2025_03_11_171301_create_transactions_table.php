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
            $table->ulid('id')->primary()->unique();
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->unsignedBigInteger('sequential_id')->index();
            $table->foreignUlid('account_id')->constrained();
            $table->foreignUlid('receivable_id')->nullable()->constrained();
            $table->foreignUlid('payable_id')->nullable()->constrained();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->timestamp('transaction_date');
            $table->string('description')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->foreignUlid('created_by')->constrained('users');
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
