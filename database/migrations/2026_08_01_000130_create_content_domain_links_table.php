<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_domain_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_publication_id')
                ->constrained('content_publications')
                ->cascadeOnDelete();
            $table->string('domain_type', 60);
            $table->string('domain_key', 190);
            $table->string('relationship', 60)->default('context');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(
                ['content_publication_id', 'domain_type', 'domain_key', 'relationship'],
                'content_domain_links_publication_subject_unique',
            );
            $table->index(
                ['domain_type', 'domain_key', 'content_publication_id'],
                'content_domain_links_subject_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_domain_links');
    }
};
