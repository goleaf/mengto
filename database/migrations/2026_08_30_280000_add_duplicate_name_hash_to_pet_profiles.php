<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->string('duplicate_name_hash', 64)->nullable();
        });

        DB::table('pet_profiles')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(500, function ($profiles): void {
                foreach ($profiles as $profile) {
                    DB::table('pet_profiles')
                        ->where('id', $profile->id)
                        ->update([
                            'duplicate_name_hash' => hash(
                                'sha256',
                                Str::lower(Str::squish((string) $profile->name)),
                            ),
                        ]);
                }
            });

        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->index(
                ['species', 'duplicate_name_hash'],
                'pet_profiles_duplicate_identity_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->dropIndex('pet_profiles_duplicate_identity_idx');
            $table->dropColumn('duplicate_name_hash');
        });
    }
};
