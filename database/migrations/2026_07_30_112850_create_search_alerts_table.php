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
        Schema::create('search_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 50)->index();
            $table->unsignedSmallInteger('radius_km');
            $table->string('region', 160);
            $table->json('channels');
            $table->json('audiences');
            $table->string('status', 30)->default('queued')->index();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->text('message');
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();

            $table->index(
                ['search_case_id', 'status', 'sent_at'],
                'search_alerts_case_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_alerts');
    }
};
