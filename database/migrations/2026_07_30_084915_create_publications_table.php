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
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 160)->unique();
            $table->string('title', 200);
            $table->text('summary');
            $table->longText('body');
            $table->string('type', 60)->index();
            $table->string('category', 80)->index();
            $table->json('tags')->nullable();
            $table->json('sources')->nullable();
            $table->text('conflict_disclosure')->nullable();
            $table->string('language', 12)->default('en');
            $table->string('status', 40)->default('draft')->index();
            $table->timestamp('last_reviewed_at')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(
                ['expert_profile_id', 'status', 'published_at'],
                'publications_profile_status_published_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
