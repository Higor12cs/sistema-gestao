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
        Schema::create('tenant_sequences', function (Blueprint $table) {
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->string('entity_type')->index();
            $table->unsignedBigInteger('last_sequence_value')->default(0);
            $table->primary(['tenant_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_sequences');
    }
};
