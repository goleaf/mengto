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
        Schema::create('expert_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('review_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reporter_key', 80)->index();
            $table->string('reason', 80)->index();
            $table->text('details')->nullable();
            $table->string('priority', 20)->default('normal')->index();
            $table->string('status', 40)->default('submitted')->index();
            $table->timestamps();

            $table->index(
                ['status', 'priority', 'created_at'],
                'expert_reports_status_priority_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_reports');
    }
};
