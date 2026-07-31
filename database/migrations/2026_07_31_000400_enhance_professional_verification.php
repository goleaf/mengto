<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credentials', function (Blueprint $table): void {
            $table->foreignId('reviewer_user_id')
                ->nullable()
                ->after('reviewed_by')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('replaces_credential_id')
                ->nullable()
                ->after('reviewer_user_id')
                ->constrained('credentials')
                ->nullOnDelete();
            $table->string('jurisdiction', 120)->nullable()->after('region');
            $table->string('credential_identifier_hash', 64)->nullable()->after('number_last_four');
            $table->string('public_summary_translation_key', 190)->nullable()->after('status');
            $table->json('scope')->nullable()->after('public_summary_translation_key');
            $table->timestamp('renewal_due_at')->nullable()->after('expires_at');
            $table->timestamp('suspended_at')->nullable()->after('verified_at');
            $table->timestamp('revoked_at')->nullable()->after('suspended_at');
            $table->string('appeal_status', 40)->nullable()->after('revoked_at');
            $table->unsignedInteger('lock_version')->default(1)->after('appeal_status');
            $table->json('metadata')->nullable()->after('verification_notes');

            $table->index(
                ['status', 'expires_at', 'expert_profile_id'],
                'credentials_status_expiry_profile_idx',
            );
            $table->index(
                ['jurisdiction', 'type', 'status'],
                'credentials_jurisdiction_type_status_idx',
            );
            $table->unique(
                ['issuer', 'type', 'credential_identifier_hash'],
                'credentials_issuer_type_identifier_unique',
            );
        });

        Schema::create('credential_verification_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credential_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('reason_translation_key', 190)->nullable();
            $table->text('internal_reason')->nullable();
            $table->string('idempotency_key', 120)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['credential_id', 'created_at'],
                'credential_events_credential_created_idx',
            );
            $table->index(
                ['actor_user_id', 'event_type', 'created_at'],
                'credential_events_actor_type_created_idx',
            );
        });

        Schema::create('credential_verification_appeals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credential_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('submitted');
            $table->text('statement');
            $table->text('reviewer_response')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['credential_id', 'status', 'created_at'],
                'credential_appeals_credential_status_created_idx',
            );
            $table->index(
                ['reviewer_user_id', 'status', 'created_at'],
                'credential_appeals_reviewer_status_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credential_verification_appeals');
        Schema::dropIfExists('credential_verification_events');

        Schema::table('credentials', function (Blueprint $table): void {
            $table->dropUnique('credentials_issuer_type_identifier_unique');
            $table->dropIndex('credentials_jurisdiction_type_status_idx');
            $table->dropIndex('credentials_status_expiry_profile_idx');
            $table->dropConstrainedForeignId('replaces_credential_id');
            $table->dropConstrainedForeignId('reviewer_user_id');
            $table->dropColumn([
                'jurisdiction',
                'credential_identifier_hash',
                'public_summary_translation_key',
                'scope',
                'renewal_due_at',
                'suspended_at',
                'revoked_at',
                'appeal_status',
                'lock_version',
                'metadata',
            ]);
        });
    }
};
