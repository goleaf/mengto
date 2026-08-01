<?php

declare(strict_types=1);

use App\Models\MedicalRecord;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table): void {
            $table->foreignId('pet_profile_id')
                ->nullable()
                ->after('owner_id')
                ->unique('medical_records_pet_profile_unique')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->string('allergy_knowledge_status', 32)
                ->default('unknown')
                ->after('blood_group')
                ->index();
            $table->string('medication_knowledge_status', 32)
                ->default('unknown')
                ->after('critical_allergies')
                ->index();
            $table->index(
                ['pet_profile_id', 'status', 'updated_at', 'id'],
                'medical_records_pet_status_idx',
            );
        });

        MedicalRecord::query()
            ->whereNull('owner_id')
            ->update([
                'owner_id' => User::query()
                    ->select('id')
                    ->whereColumn('users.actor_key', 'medical_records.owner_key')
                    ->limit(1),
            ]);

        MedicalRecord::query()
            ->whereNull('pet_profile_id')
            ->update([
                'pet_profile_id' => PetProfile::query()
                    ->withTrashed()
                    ->select('id')
                    ->whereColumn('pet_profiles.user_id', 'medical_records.owner_id')
                    ->whereColumn('pet_profiles.slug', 'medical_records.pet_profile_key')
                    ->limit(1),
            ]);
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table): void {
            $table->dropIndex('medical_records_pet_status_idx');
            $table->dropIndex(['allergy_knowledge_status']);
            $table->dropIndex(['medication_knowledge_status']);
            $table->dropUnique('medical_records_pet_profile_unique');
            $table->dropConstrainedForeignId('pet_profile_id');
            $table->dropColumn([
                'allergy_knowledge_status',
                'medication_knowledge_status',
            ]);
        });
    }
};
