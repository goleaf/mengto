<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('profile_key', 80)->unique();
            $table->string('slug', 80);
            $table->string('name', 120);
            $table->string('species', 80);
            $table->string('breed', 120)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('visibility', 24)->default('public');
            $table->string('status', 24)->default('active');
            $table->text('profile_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->index(['visibility', 'status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_profiles');
    }
};
