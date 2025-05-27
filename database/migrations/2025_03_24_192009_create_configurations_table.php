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
        Schema::create('configurations', function (Blueprint $table) {
            $table->ulid('id')->primary()->unique();
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->unsignedBigInteger('sequential_id')->index();
            $table->string('description')->nullable();
            $table->string('name')->nullable();
            $table->string('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
