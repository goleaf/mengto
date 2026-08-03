<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('slug', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->string('name', 180);
            $table->text('summary')->nullable();
            $table->string('type', 40);
            $table->string('status', 30)->default('active');
            $table->string('verification_status', 30)->default('not_assessed');
            $table->string('verification_source', 120)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->string('default_locale', 10)->default('en');
            $table->string('public_region', 160)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignId('suspended_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason_code', 120)->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type', 'name', 'id'], 'organizations_status_type_name_idx');
            $table->index(
                ['verification_status', 'verification_expires_at', 'id'],
                'organizations_verification_expiry_idx',
            );
            $table->index(['owner_user_id', 'status', 'id'], 'organizations_owner_status_idx');
            $table->index('suspended_by_user_id', 'organizations_suspended_by_idx');
        });

        Schema::create('organization_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('role', 40)->default('member');
            $table->string('status', 30)->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('removed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('removed_at')->nullable();
            $table->string('removal_reason_code', 120)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'user_id'], 'organization_memberships_org_user_unique');
            $table->index(
                ['user_id', 'status', 'expires_at', 'id'],
                'organization_memberships_user_state_expiry_idx',
            );
            $table->index(
                ['organization_id', 'status', 'role', 'id'],
                'organization_memberships_org_state_role_idx',
            );
            $table->index('invited_by_user_id', 'organization_memberships_invited_by_idx');
            $table->index('removed_by_user_id', 'organization_memberships_removed_by_idx');
        });

        Schema::create('organization_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->foreignId('invited_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->char('token_hash', 64)->unique();
            $table->string('role', 40);
            $table->string('status', 30)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('revoked_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(
                ['invited_user_id', 'status', 'expires_at', 'id'],
                'organization_invitations_user_state_expiry_idx',
            );
            $table->index(
                ['organization_id', 'status', 'expires_at', 'id'],
                'organization_invitations_org_state_expiry_idx',
            );
            $table->index('invited_by_user_id', 'organization_invitations_invited_by_idx');
            $table->index('revoked_by_user_id', 'organization_invitations_revoked_by_idx');
        });

        Schema::create('organization_restrictions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->foreignId('applied_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('capability', 50);
            $table->string('reason_code', 120);
            $table->string('idempotency_key', 190)->unique();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('revoked_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(
                ['organization_id', 'capability', 'starts_at', 'ends_at', 'revoked_at', 'id'],
                'organization_restrictions_active_capability_idx',
            );
            $table->index(
                ['applied_by_user_id', 'created_at', 'id'],
                'organization_restrictions_actor_created_idx',
            );
            $table->index('revoked_by_user_id', 'organization_restrictions_revoked_by_idx');
        });

        Schema::create('organization_audit_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('subject_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('reason_code', 120);
            $table->string('summary_translation_key', 190);
            $table->text('metadata')->nullable();
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(
                ['organization_id', 'created_at', 'id'],
                'organization_audit_org_created_idx',
            );
            $table->index(
                ['actor_user_id', 'event_type', 'created_at', 'id'],
                'organization_audit_actor_type_idx',
            );
            $table->index(
                ['subject_user_id', 'created_at', 'id'],
                'organization_audit_subject_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_audit_events');
        Schema::dropIfExists('organization_restrictions');
        Schema::dropIfExists('organization_invitations');
        Schema::dropIfExists('organization_memberships');
        Schema::dropIfExists('organizations');
    }
};
