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
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('request_kind', 40)->default('purchase')->after('status')->index();
            $table->unsignedInteger('quantity')->default(1)->after('request_kind');
            $table->decimal('offered_price', 10, 2)->nullable()->after('quantity');
            $table->timestamp('rental_starts_at')->nullable()->after('proposed_at');
            $table->timestamp('rental_ends_at')->nullable()->after('rental_starts_at');
            $table->json('questionnaire')->nullable()->after('rental_ends_at');
            $table->boolean('terms_accepted')->default(false)->after('questionnaire');
            $table->boolean('privacy_accepted')->default(false)->after('terms_accepted');

            $table->index(
                ['listing_id', 'request_kind', 'status', 'created_at'],
                'reservations_listing_kind_status_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_listing_kind_status_idx');
            $table->dropIndex('reservations_request_kind_index');
            $table->dropColumn([
                'request_kind',
                'quantity',
                'offered_price',
                'rental_starts_at',
                'rental_ends_at',
                'questionnaire',
                'terms_accepted',
                'privacy_accepted',
            ]);
        });
    }
};
