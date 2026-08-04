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
        Schema::table('pet_profiles', function (Blueprint $table) {
            $table->string('size_category', 32)
                ->nullable()
                ->after('breed_origin_type');
            $table->index(
                ['size_category', 'status', 'id'],
                'pet_profiles_size_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table) {
            $table->dropIndex('pet_profiles_size_status_idx');
            $table->dropColumn('size_category');
        });
    }
};
