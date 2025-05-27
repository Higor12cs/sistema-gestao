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
        Schema::create('purchases', function (Blueprint $table) {
            $table->ulid('id')->primary()->unique();
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->unsignedBigInteger('sequential_id')->index();
            $table->foreignUlid('supplier_id')->constrained();
            $table->timestamp('issue_date');
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('fees', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->text('observation')->nullable();
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
