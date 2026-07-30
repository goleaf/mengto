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
        Schema::create('care_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('care_routine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 180);
            $table->string('type', 40);
            $table->string('assignee_key', 80)->nullable();
            $table->string('assignee_name', 120)->nullable();
            $table->timestamp('due_at');
            $table->string('timezone', 64);
            $table->string('repeat_rule', 120)->nullable();
            $table->string('priority', 32)->default('normal');
            $table->string('status', 32)->default('planned');
            $table->text('instructions')->nullable();
            $table->boolean('requires_individual_confirmation')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->string('completed_by_key', 80)->nullable();
            $table->string('completed_by_name', 120)->nullable();
            $table->text('completion_note')->nullable();
            $table->string('created_by_key', 80);
            $table->string('created_by_name', 120);
            $table->timestamps();

            $table->index(['care_journal_id', 'status', 'due_at']);
            $table->index(['assignee_key', 'status', 'due_at']);
            $table->index(['care_routine_id', 'due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_tasks');
    }
};
