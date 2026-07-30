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
        Schema::create('expert_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->string('user_key', 80);
            $table->boolean('is_saved')->default(false);
            $table->boolean('is_subscribed')->default(false);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['expert_profile_id', 'user_key']);
            $table->index(['user_key', 'is_saved', 'is_subscribed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_engagements');
    }
};
