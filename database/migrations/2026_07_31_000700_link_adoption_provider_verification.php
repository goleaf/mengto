<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adoption_cases', function (Blueprint $table): void {
            $table->foreignId('provider_expert_profile_id')
                ->nullable()
                ->after('provider_type')
                ->constrained('expert_profiles')
                ->nullOnDelete();
            $table->foreignId('provider_credential_id')
                ->nullable()
                ->after('provider_expert_profile_id')
                ->constrained('credentials')
                ->nullOnDelete();
            $table->string('provider_identity_status', 40)
                ->default('unverified')
                ->after('provider_credential_id');
            $table->timestamp('provider_verified_at')
                ->nullable()
                ->after('provider_verified');
            $table->timestamp('provider_verification_expires_at')
                ->nullable()
                ->after('provider_verified_at');

            $table->index(
                ['provider_identity_status', 'status', 'published_at', 'id'],
                'adoption_cases_provider_identity_status_idx',
            );
            $table->index(
                ['provider_expert_profile_id', 'status', 'id'],
                'adoption_cases_provider_profile_status_idx',
            );
            $table->index(
                ['provider_credential_id', 'status', 'id'],
                'adoption_cases_provider_credential_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('adoption_cases', function (Blueprint $table): void {
            $table->dropIndex('adoption_cases_provider_identity_status_idx');
            $table->dropIndex('adoption_cases_provider_profile_status_idx');
            $table->dropIndex('adoption_cases_provider_credential_status_idx');
            $table->dropConstrainedForeignId('provider_credential_id');
            $table->dropConstrainedForeignId('provider_expert_profile_id');
            $table->dropColumn([
                'provider_identity_status',
                'provider_verified_at',
                'provider_verification_expires_at',
            ]);
        });
    }
};
