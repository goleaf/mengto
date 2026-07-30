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
        Schema::create('care_access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_journal_id')->constrained()->cascadeOnDelete();
            $table->string('granted_by_key', 80);
            $table->string('recipient_key', 80)->nullable();
            $table->string('recipient_name', 160);
            $table->string('recipient_role', 80);
            $table->string('label', 180);
            $table->char('token_hash', 64)->unique();
            $table->json('sections');
            $table->json('permissions');
            $table->boolean('allow_add')->default(false);
            $table->boolean('allow_location')->default(false);
            $table->boolean('allow_media')->default(false);
            $table->unsignedSmallInteger('max_views')->default(20);
            $table->unsignedSmallInteger('views_used')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['care_journal_id', 'revoked_at', 'expires_at']);
            $table->index(['recipient_key', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_access_grants');
    }
};
