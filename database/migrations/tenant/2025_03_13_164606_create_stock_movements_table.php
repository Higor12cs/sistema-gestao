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
            $table->id();
            $table->foreignId('stock_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->enum('source_type', ['purchase', 'order', 'adjustment', 'initial']);
            $table->ulid('source_id')->nullable();
            $table->decimal('previous_quantity', 10, 2);
            $table->decimal('quantity', 10, 2);
            $table->decimal('new_quantity', 10, 2);
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
        Schema::dropIfExists('stock_movements');
    }
};
