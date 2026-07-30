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
        Schema::create('document_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->string('owner_key', 80)->index();
            $table->string('label', 180);
            $table->string('document_type', 80);
            $table->string('file_path');
            $table->json('permissions')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();

            $table->index(
                ['expert_profile_id', 'revoked_at', 'expires_at'],
                'document_grants_profile_access_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_grants');
    }
};
