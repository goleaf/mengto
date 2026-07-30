<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_lifecycle_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('smart_device_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 32);
            $table->string('status', 32);
            $table->string('created_by_key', 80);
            $table->string('version_from', 80)->nullable();
            $table->string('version_to', 80)->nullable();
            $table->string('severity', 24)->default('normal');
            $table->text('details')->nullable();
            $table->timestamp('effective_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(
                ['smart_device_id', 'kind', 'status', 'effective_at'],
                'device_lifecycle_device_kind_status_idx',
            );
            $table->index(
                ['kind', 'status', 'effective_at'],
                'device_lifecycle_kind_status_effective_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_lifecycle_records');
    }
};
