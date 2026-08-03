<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table): void {
            $table->string('catalog_category', 40)->nullable()->after('type');
            $table->string('public_phone', 40)->nullable()->after('public_address');
            $table->string('public_website', 2048)->nullable()->after('public_phone');
            $table->string('public_email', 255)->nullable()->after('public_website');

            $table->index(
                ['catalog_category', 'visibility', 'status', 'id'],
                'places_catalog_visibility_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table): void {
            $table->dropIndex('places_catalog_visibility_status_idx');
            $table->dropColumn([
                'catalog_category',
                'public_phone',
                'public_website',
                'public_email',
            ]);
        });
    }
};
