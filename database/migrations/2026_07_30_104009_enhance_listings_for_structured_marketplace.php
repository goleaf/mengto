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
        Schema::table('listings', function (Blueprint $table) {
            $table->string('seller_type', 40)->default('private')->after('business_name')->index();
            $table->boolean('is_verified_seller')->default(false)->after('seller_type')->index();
            $table->string('brand', 120)->nullable()->after('category')->index();
            $table->string('model', 120)->nullable()->after('brand');
            $table->string('material', 120)->nullable()->after('model');
            $table->unsignedInteger('quantity')->default(1)->after('is_free');
            $table->string('availability', 40)->default('in-stock')->after('quantity')->index();
            $table->string('age_group', 40)->nullable()->after('pet_size');
            $table->json('attributes')->nullable()->after('age_group');
            $table->text('defects')->nullable()->after('attributes');
            $table->string('hygiene_status', 60)->nullable()->after('defects');
            $table->boolean('sealed_package')->default(false)->after('hygiene_status');
            $table->text('return_policy')->nullable()->after('meetup_notes');
            $table->string('video_url')->nullable()->after('gallery');
            $table->string('moderation_status', 40)->default('approved')->after('safety_status')->index();
            $table->json('risk_flags')->nullable()->after('moderation_status');
            $table->timestamp('expires_at')->nullable()->after('completed_at')->index();

            $table->index(
                ['status', 'availability', 'condition', 'published_at', 'id'],
                'listings_availability_condition_idx',
            );
            $table->index(
                ['seller_type', 'is_verified_seller', 'status', 'published_at'],
                'listings_seller_verification_idx',
            );
            $table->index(
                ['moderation_status', 'status', 'published_at'],
                'listings_moderation_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_availability_condition_idx');
            $table->dropIndex('listings_seller_verification_idx');
            $table->dropIndex('listings_moderation_status_idx');
            $table->dropIndex('listings_seller_type_index');
            $table->dropIndex('listings_is_verified_seller_index');
            $table->dropIndex('listings_brand_index');
            $table->dropIndex('listings_availability_index');
            $table->dropIndex('listings_moderation_status_index');
            $table->dropIndex('listings_expires_at_index');
            $table->dropColumn([
                'seller_type',
                'is_verified_seller',
                'brand',
                'model',
                'material',
                'quantity',
                'availability',
                'age_group',
                'attributes',
                'defects',
                'hygiene_status',
                'sealed_package',
                'return_policy',
                'video_url',
                'moderation_status',
                'risk_flags',
                'expires_at',
            ]);
        });
    }
};
