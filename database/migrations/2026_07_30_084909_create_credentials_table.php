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
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60)->index();
            $table->string('title', 180);
            $table->string('issuer', 180);
            $table->string('region', 120)->nullable();
            $table->string('number_last_four', 4)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable()->index();
            $table->string('status', 40)->default('submitted')->index();
            $table->string('file_path')->nullable();
            $table->string('reviewed_by', 120)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('verification_notes')->nullable();
            $table->timestamps();

            $table->index(
                ['expert_profile_id', 'status', 'type'],
                'credentials_profile_status_type_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};
