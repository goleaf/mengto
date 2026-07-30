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
        Schema::create('search_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->cascadeOnDelete();
            $table->string('author_key', 80);
            $table->string('author_name', 120);
            $table->string('type', 50)->index();
            $table->string('visibility', 30)->default('public')->index();
            $table->string('title', 160);
            $table->text('body')->nullable();
            $table->string('public_area', 160)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(
                ['search_case_id', 'visibility', 'occurred_at', 'id'],
                'search_updates_case_visibility_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_updates');
    }
};
