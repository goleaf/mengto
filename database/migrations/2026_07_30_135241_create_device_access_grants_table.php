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
        Schema::create('device_access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_device_id')->constrained()->cascadeOnDelete();
            $table->string('granted_by_key', 80);
            $table->string('recipient_key', 80)->nullable();
            $table->string('recipient_name', 120);
            $table->string('recipient_role', 40);
            $table->string('label', 140);
            $table->string('token_hash', 64)->unique();
            $table->json('permissions');
            $table->boolean('allow_location')->default(false);
            $table->boolean('allow_camera')->default(false);
            $table->boolean('allow_commands')->default(false);
            $table->boolean('allow_audio')->default(false);
            $table->unsignedInteger('max_views')->default(20);
            $table->unsignedInteger('views_used')->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['smart_device_id', 'revoked_at', 'expires_at']);
            $table->index(['recipient_key', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_access_grants');
    }
};
