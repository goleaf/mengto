<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scope', 16);
            $table->string('category', 32);
            $table->string('target_key', 160);
            $table->string('reason_code', 40)->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'scope', 'category', 'target_key'],
                'discovery_preferences_user_target_unique',
            );
            $table->index(
                ['user_id', 'category', 'scope'],
                'discovery_preferences_user_category_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_preferences');
    }
};
