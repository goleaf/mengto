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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 120);
            $table->string('name', 160);
            $table->string('type', 80)->index();
            $table->string('format', 60)->index();
            $table->text('description');
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('pricing_model', 40)->default('fixed');
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->json('preparation')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->unsignedSmallInteger('follow_up_days')->default(0);
            $table->boolean('requires_payment')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->string('status', 40)->default('active')->index();
            $table->timestamps();

            $table->unique(['expert_profile_id', 'slug']);
            $table->index(
                ['expert_profile_id', 'status', 'format'],
                'services_profile_status_format_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
