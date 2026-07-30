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
        Schema::create('search_sectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('label', 120);
            $table->string('status', 40)->default('unchecked')->index();
            $table->unsignedTinyInteger('priority')->default(2)->index();
            $table->json('map_bounds')->nullable();
            $table->text('risk_notes')->nullable();
            $table->text('access_notes')->nullable();
            $table->string('checked_by_key', 80)->nullable();
            $table->timestamp('checked_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['search_case_id', 'code'], 'search_sectors_case_code_unique');
            $table->index(
                ['search_case_id', 'status', 'priority'],
                'search_sectors_case_status_priority_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_sectors');
    }
};
