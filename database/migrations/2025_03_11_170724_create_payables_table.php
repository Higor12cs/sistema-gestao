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
        Schema::create('payables', function (Blueprint $table) {
            $table->ulid('id')->primary()->unique();
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->unsignedBigInteger('sequential_id')->index();
            $table->foreignUlid('purchase_id')->nullable()->constrained();
            $table->foreignUlid('chart_account_id')->constrained();
            $table->foreignUlid('payment_method_id')->constrained();
            $table->boolean('is_manual')->default(false);
            $table->foreignUlid('supplier_id')->constrained();
            $table->timestamp('issue_date');
            $table->timestamp('due_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('fees', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->string('status');
            $table->text('description')->nullable();
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payables');
    }
};
