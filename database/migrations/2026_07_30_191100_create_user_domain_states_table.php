<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_domain_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('namespace', 64);
            $table->unsignedBigInteger('version')->default(1);
            $table->text('payload');
            $table->timestamps();

            $table->unique(['user_id', 'namespace']);
            $table->index(['namespace', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_domain_states');
    }
};
