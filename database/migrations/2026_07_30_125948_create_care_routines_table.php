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
        Schema::create('care_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_journal_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('period', 40)->default('daily');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->json('days')->nullable();
            $table->time('start_time')->nullable();
            $table->string('timezone', 64);
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('version')->default(1);
            $table->text('instructions')->nullable();
            $table->string('created_by_key', 80);
            $table->string('created_by_name', 120);
            $table->timestamps();

            $table->index(['care_journal_id', 'status', 'starts_on']);
            $table->index(['care_journal_id', 'period', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_routines');
    }
};
