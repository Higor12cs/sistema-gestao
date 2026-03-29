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
        Schema::create('payables_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payable_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->timestamp('payment_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('fees', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
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
        Schema::dropIfExists('payable_paids');
    }
};
