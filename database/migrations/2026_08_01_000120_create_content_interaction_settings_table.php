<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_interaction_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_publication_id')
                ->unique()
                ->constrained('content_publications')
                ->cascadeOnDelete();
            $table->boolean('allow_comments')->default(true);
            $table->boolean('allow_reactions')->default(true);
            $table->boolean('allow_reposts')->default(true);
            $table->boolean('allow_external_sharing')->default(false);
            $table->boolean('allow_media_downloads')->default(false);
            $table->boolean('allow_mentions')->default(true);
            $table->boolean('is_searchable')->default(true);
            $table->boolean('allow_external_indexing')->default(false);
            $table->boolean('show_reaction_counts')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_interaction_settings');
    }
};
