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
        Schema::create('search_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sighting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_key', 80);
            $table->string('reason', 80);
            $table->text('details')->nullable();
            $table->string('priority', 20)->default('normal')->index();
            $table->string('status', 30)->default('open')->index();
            $table->timestamps();

            $table->index(
                ['search_case_id', 'status', 'created_at'],
                'search_reports_case_status_idx',
            );
            $table->index(
                ['priority', 'status', 'created_at'],
                'search_reports_priority_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_reports');
    }
};
