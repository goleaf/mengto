<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('granted_by_key', 80)->index();
            $table->string('recipient_key', 80)->nullable()->index();
            $table->string('recipient_name', 160);
            $table->string('recipient_role', 60)->index();
            $table->string('label', 180);
            $table->string('token_hash', 64)->unique();
            $table->json('sections');
            $table->json('permissions');
            $table->boolean('allow_download')->default(false);
            $table->boolean('allow_edit')->default(false);
            $table->unsignedSmallInteger('max_views')->default(10);
            $table->unsignedSmallInteger('views_used')->default(0);
            $table->timestamp('expires_at')->index();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();

            $table->index(
                ['medical_record_id', 'revoked_at', 'expires_at'],
                'medical_access_record_active_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_access_grants');
    }
};
