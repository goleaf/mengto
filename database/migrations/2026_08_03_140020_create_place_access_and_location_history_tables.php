<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_access_grants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->constrained('places')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('forum_events')->cascadeOnDelete();
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('purpose', 50);
            $table->string('status', 30)->default('active');
            $table->boolean('may_view_exact_location')->default(true);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason_code', 120)->nullable();
            $table->string('idempotency_key', 190)->unique();
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index(['place_id', 'user_id', 'status', 'valid_until', 'id'], 'place_grants_access_lookup_idx');
            $table->index(['user_id', 'status', 'valid_until', 'id'], 'place_grants_user_access_idx');
            $table->index(['event_id', 'status', 'valid_until', 'id'], 'place_grants_event_lookup_idx');
            $table->index('issued_by_user_id', 'place_grants_issued_by_idx');
            $table->index('revoked_by_user_id', 'place_grants_revoked_by_idx');
        });

        Schema::create('place_access_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('place_access_grant_id')->nullable()->constrained('place_access_grants')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('forum_events')->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('purpose', 50)->nullable();
            $table->string('channel', 80);
            $table->text('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['place_id', 'created_at', 'id'], 'place_access_audits_place_created_idx');
            $table->index(['user_id', 'created_at', 'id'], 'place_access_audits_user_created_idx');
            $table->index('place_access_grant_id', 'place_access_audits_grant_idx');
            $table->index('event_id', 'place_access_audits_event_idx');
        });

        Schema::create('place_location_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->string('public_region', 160);
            $table->string('public_address', 500)->nullable();
            $table->decimal('public_latitude', 9, 6)->nullable();
            $table->decimal('public_longitude', 9, 6)->nullable();
            $table->text('exact_address')->nullable();
            $table->text('exact_latitude')->nullable();
            $table->text('exact_longitude')->nullable();
            $table->text('private_instructions')->nullable();
            $table->string('reason_code', 120);
            $table->timestamp('created_at');

            $table->unique(['place_id', 'version'], 'place_location_versions_place_version_unique');
            $table->index(['place_id', 'created_at', 'id'], 'place_location_versions_place_created_idx');
            $table->index('changed_by_user_id', 'place_location_versions_changed_by_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_location_versions');
        Schema::dropIfExists('place_access_audits');
        Schema::dropIfExists('place_access_grants');
    }
};
