<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('post_key', 80);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->index(['post_key', 'position']);
        });

        Schema::create('photo_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_asset_id')
                ->constrained('photo_assets')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->text('body');
            $table->char('idempotency_key', 26);
            $table->timestamps();

            $table->index(['photo_asset_id', 'id']);
            $table->index('user_id');
            $table->unique(['user_id', 'idempotency_key']);
        });

        Schema::create('photo_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_asset_id')
                ->constrained('photo_assets')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('reaction', 24);
            $table->timestamps();

            $table->unique(['photo_asset_id', 'user_id']);
            $table->index(['photo_asset_id', 'reaction']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_reactions');
        Schema::dropIfExists('photo_comments');
        Schema::dropIfExists('photo_assets');
    }
};
