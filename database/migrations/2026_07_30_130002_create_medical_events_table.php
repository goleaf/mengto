<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40)->index();
            $table->string('title', 180);
            $table->timestamp('occurred_at')->index();
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('status', 40)->default('active')->index();
            $table->string('source_type', 40)->index();
            $table->string('source_name', 160);
            $table->string('source_reference', 160)->nullable();
            $table->string('verification_status', 40)->index();
            $table->text('summary')->nullable();
            $table->text('details')->nullable();
            $table->string('created_by_key', 80)->index();
            $table->string('created_by_name', 120);
            $table->string('confirmed_by_name', 160)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('follow_up_at')->nullable()->index();
            $table->boolean('is_critical')->default(false)->index();
            $table->timestamps();

            $table->index(
                ['medical_record_id', 'occurred_at', 'id'],
                'medical_events_record_occurred_idx',
            );
            $table->index(
                ['medical_record_id', 'type', 'occurred_at'],
                'medical_events_record_type_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_events');
    }
};
