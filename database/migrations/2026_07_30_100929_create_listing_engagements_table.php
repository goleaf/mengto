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
        Schema::create('listing_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('user_key', 80);
            $table->boolean('is_saved')->default(false);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['listing_id', 'user_key']);
            $table->index(['user_key', 'is_saved', 'updated_at'], 'listing_engagements_user_saved_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_engagements');
    }
};
