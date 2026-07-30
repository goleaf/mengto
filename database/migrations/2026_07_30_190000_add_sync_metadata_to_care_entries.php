<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_entries', function (Blueprint $table): void {
            $table->timestamp('source_recorded_at')->nullable();
            $table->string('source_timezone', 64)->nullable();
            $table->string('sync_status', 24)->default('direct');
            $table->timestamp('synchronized_at')->nullable();
            $table->index(
                ['care_journal_id', 'sync_status', 'source_recorded_at'],
                'care_entries_journal_sync_recorded_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('care_entries', function (Blueprint $table): void {
            $table->dropIndex('care_entries_journal_sync_recorded_index');
            $table->dropColumn([
                'source_recorded_at',
                'source_timezone',
                'sync_status',
                'synchronized_at',
            ]);
        });
    }
};
