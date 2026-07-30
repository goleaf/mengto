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
        Schema::create('search_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('search_sector_id')->nullable()->constrained()->nullOnDelete();
            $table->string('created_by_key', 80);
            $table->string('assignee_key', 80)->nullable()->index();
            $table->string('assignee_name', 120)->nullable();
            $table->string('type', 50)->index();
            $table->string('title', 140);
            $table->text('description');
            $table->string('status', 40)->default('open')->index();
            $table->string('safety_level', 30)->default('standard')->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('result')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->index(
                ['search_case_id', 'status', 'due_at', 'id'],
                'search_tasks_case_status_due_idx',
            );
            $table->index(
                ['search_sector_id', 'status', 'id'],
                'search_tasks_sector_status_idx',
            );
            $table->index(
                ['assignee_key', 'status', 'starts_at'],
                'search_tasks_assignee_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_tasks');
    }
};
