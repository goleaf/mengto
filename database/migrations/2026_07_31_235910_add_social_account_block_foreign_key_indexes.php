<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_account_blocks', function (Blueprint $table): void {
            $table->index('source_actor_id', 'social_account_blocks_source_actor_fk_idx');
            $table->index('target_actor_id', 'social_account_blocks_target_actor_fk_idx');
            $table->index('created_by_user_id', 'social_account_blocks_creator_fk_idx');
            $table->index('revoked_by_user_id', 'social_account_blocks_revoker_fk_idx');
        });
    }

    public function down(): void
    {
        Schema::table('social_account_blocks', function (Blueprint $table): void {
            $table->dropIndex('social_account_blocks_source_actor_fk_idx');
            $table->dropIndex('social_account_blocks_target_actor_fk_idx');
            $table->dropIndex('social_account_blocks_creator_fk_idx');
            $table->dropIndex('social_account_blocks_revoker_fk_idx');
        });
    }
};
