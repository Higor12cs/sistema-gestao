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
        Schema::create('chart_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary()->unique();
            $table->foreignUlid('tenant_id')->index()->nullable()->constrained();
            $table->unsignedBigInteger('sequential_id')->index();
            $table->ulid('parent_id')->nullable()->index();
            $table->string('code')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('allows_transactions')->default(true);
            $table->boolean('active')->default(true);
            $table->integer('level')->default(1);
            $table->integer('order')->default(0);
            $table->boolean('default_receivable')->default(false);
            $table->boolean('default_purchase')->default(false);
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['tenant_id', 'parent_id', 'order']);
            $table->unique(['tenant_id', 'code']);
        });

        Schema::table('chart_accounts', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('chart_accounts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_accounts');
    }
};
