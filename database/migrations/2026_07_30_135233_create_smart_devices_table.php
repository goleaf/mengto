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
        Schema::create('smart_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_key', 80);
            $table->string('slug', 140)->unique();
            $table->string('name', 120);
            $table->string('type', 40);
            $table->string('brand', 100)->nullable();
            $table->string('model', 120)->nullable();
            $table->text('serial_number')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->string('public_zone_label', 160)->nullable();
            $table->text('private_location_label')->nullable();
            $table->string('privacy', 24)->default('private');
            $table->string('status', 32)->default('active');
            $table->string('connection_status', 32)->default('offline');
            $table->string('operating_mode', 40)->default('normal');
            $table->string('connection_type', 40)->nullable();
            $table->string('firmware_version', 80)->nullable();
            $table->unsignedTinyInteger('battery_percent')->nullable();
            $table->smallInteger('signal_strength')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_location_at')->nullable();
            $table->text('current_latitude')->nullable();
            $table->text('current_longitude')->nullable();
            $table->decimal('location_accuracy_meters', 10, 2)->nullable();
            $table->text('subscription_details')->nullable();
            $table->date('purchased_on')->nullable();
            $table->date('warranty_ends_on')->nullable();
            $table->boolean('has_backup_power')->default(false);
            $table->boolean('supports_local_operation')->default(false);
            $table->boolean('requires_cloud')->default(true);
            $table->boolean('is_medical_device')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_reported_stolen')->default(false);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->index(['owner_key', 'status', 'updated_at']);
            $table->index(['type', 'status']);
            $table->index(['connection_status', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_devices');
    }
};
