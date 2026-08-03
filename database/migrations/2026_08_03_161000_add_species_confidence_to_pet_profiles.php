<?php

declare(strict_types=1);

use App\Enums\PetSpeciesConfidence;
use App\Models\PetProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->string('species_confidence', 24)
                ->default(PetSpeciesConfidence::Confirmed->value)
                ->after('species');
        });

        PetProfile::query()
            ->where('species', 'unknown')
            ->update(['species_confidence' => PetSpeciesConfidence::Unidentified->value]);
    }

    public function down(): void
    {
        Schema::table('pet_profiles', function (Blueprint $table): void {
            $table->dropColumn('species_confidence');
        });
    }
};
