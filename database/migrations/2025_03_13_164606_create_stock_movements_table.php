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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary()->unique();
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->unsignedBigInteger('sequential_id')->index();
            $table->foreignUlid('stock_id')->constrained();
            $table->foreignUlid('product_id')->constrained();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->enum('source_type', ['purchase', 'order', 'adjustment', 'initial']);
            $table->ulid('source_id')->nullable();
            $table->decimal('previous_quantity', 10, 2);
            $table->decimal('quantity', 10, 2);
            $table->decimal('new_quantity', 10, 2);
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
        Schema::dropIfExists('stock_movements');
    }
};
