<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->index();
            $table->string('title', 180);
            $table->timestamp('due_at')->index();
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('priority', 30)->default('normal')->index();
            $table->string('status', 30)->index();
            $table->text('recipients')->nullable();
            $table->text('instructions')->nullable();
            $table->string('related_type', 60)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmed_by_key', 80)->nullable();
            $table->string('created_by_key', 80);
            $table->timestamps();

            $table->index(
                ['medical_record_id', 'status', 'due_at'],
                'medical_reminders_record_status_due_idx',
            );
            $table->index(
                ['related_type', 'related_id'],
                'medical_reminders_related_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_reminders');
    }
};
