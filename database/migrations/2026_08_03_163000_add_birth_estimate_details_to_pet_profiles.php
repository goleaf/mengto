<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->unsignedSmallInteger('estimated_age_months')
                ->nullable()
                ->after('birth_date_precision');
            $table->timestamp('estimated_age_recorded_at')
                ->nullable()
                ->after('estimated_age_months');
            $table->unsignedTinyInteger('birthday_celebration_month')
                ->nullable()
                ->after('estimated_age_recorded_at');
            $table->unsignedTinyInteger('birthday_celebration_day')
                ->nullable()
                ->after('birthday_celebration_month');
        });
    }

    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'estimated_age_months',
                'estimated_age_recorded_at',
                'birthday_celebration_month',
                'birthday_celebration_day',
            ]);
        });
    }
};
