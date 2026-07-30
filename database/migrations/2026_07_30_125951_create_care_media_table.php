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
        Schema::create('care_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('care_entry_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 40)->default('local');
            $table->string('path', 500);
            $table->string('mime_type', 120);
            $table->text('original_name');
            $table->unsignedBigInteger('size_bytes');
            $table->text('alt_text')->nullable();
            $table->string('sensitivity', 32)->default('private');
            $table->string('created_by_key', 80);
            $table->timestamps();

            $table->index(['care_journal_id', 'created_at']);
            $table->index(['care_entry_id', 'sensitivity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_media');
    }
};
