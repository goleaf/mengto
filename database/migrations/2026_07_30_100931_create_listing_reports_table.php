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
        Schema::create('listing_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_key', 80);
            $table->string('reason', 80);
            $table->text('details')->nullable();
            $table->string('priority', 20)->default('normal')->index();
            $table->string('status', 40)->default('open')->index();
            $table->timestamps();

            $table->index(['listing_id', 'status', 'created_at'], 'listing_reports_listing_status_idx');
            $table->index(['priority', 'status', 'created_at'], 'listing_reports_priority_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_reports');
    }
};
