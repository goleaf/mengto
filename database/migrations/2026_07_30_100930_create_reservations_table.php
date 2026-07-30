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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requester_key', 80);
            $table->string('requester_name', 120);
            $table->uuid('idempotency_key')->unique();
            $table->string('status', 40)->index();
            $table->text('message');
            $table->string('exchange_method', 40);
            $table->timestamp('proposed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'status', 'created_at'], 'reservations_listing_status_idx');
            $table->index(['requester_key', 'status', 'created_at'], 'reservations_requester_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
