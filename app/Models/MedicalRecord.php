<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicalKnowledgeStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetProfilePermission;
use Database\Factories\MedicalRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read Collection<int, MedicalAccessGrant> $accessGrants
 * @property Carbon|null $birth_date
 * @property bool $birth_date_estimated
 * @property string|null $blood_group
 * @property string|null $breed
 * @property array<array-key, mixed>|null $chronic_conditions
 * @property Carbon|null $created_at
 * @property array<array-key, mixed>|null $critical_allergies
 * @property int|null $current_weight_grams
 * @property-read Collection<int, MedicalDocument> $documents
 * @property-read Collection<int, MedicationDose> $doses
 * @property array<array-key, mixed>|null $emergency_contact
 * @property string|null $emergency_notes
 * @property-read Collection<int, MedicalEvent> $events
 * @property int $id
 * @property string|null $image_url
 * @property Carbon|null $last_visit_at
 * @property int $lock_version
 * @property-read Collection<int, Medication> $medications
 * @property Carbon|null $microchip_checked_on
 * @property string|null $microchip_number
 * @property string $microchip_status
 * @property Carbon|null $next_appointment_at
 * @property int|null $owner_id
 * @property int|null $pet_profile_id
 * @property string $owner_key
 * @property string $pet_name
 * @property string $pet_profile_key
 * @property string|null $primary_clinic_contact
 * @property string|null $primary_clinic_name
 * @property string $privacy
 * @property-read Collection<int, MedicalReminder> $reminders
 * @property string $reproductive_status
 * @property string|null $sex
 * @property string $slug
 * @property string $species
 * @property string $status
 * @property string $timezone
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Vaccination> $vaccinations
 * @property-read Collection<int, WeightEntry> $weightEntries
 * @property-read PetProfile|null $petProfile
 * @property MedicalKnowledgeStatus $allergy_knowledge_status
 * @property MedicalKnowledgeStatus $medication_knowledge_status
 */
class MedicalRecord extends Model
{
    /** @use HasFactory<MedicalRecordFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'pet_profile_id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
        'species', 'breed', 'birth_date', 'birth_date_estimated', 'sex',
        'reproductive_status', 'current_weight_grams', 'image_url', 'status',
        'privacy', 'timezone', 'microchip_status', 'microchip_number',
        'microchip_checked_on', 'blood_group', 'allergy_knowledge_status', 'critical_allergies',
        'medication_knowledge_status',
        'chronic_conditions', 'emergency_notes', 'primary_clinic_name',
        'primary_clinic_contact', 'emergency_contact', 'last_visit_at',
        'next_appointment_at', 'lock_version', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'pet_profile_id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
        'species', 'breed', 'birth_date', 'birth_date_estimated', 'sex',
        'reproductive_status', 'current_weight_grams', 'image_url', 'status',
        'privacy', 'timezone', 'microchip_status', 'microchip_number',
        'microchip_checked_on', 'blood_group', 'allergy_knowledge_status', 'critical_allergies',
        'medication_knowledge_status',
        'chronic_conditions', 'emergency_notes', 'primary_clinic_name',
        'primary_clinic_contact', 'emergency_contact', 'last_visit_at',
        'next_appointment_at', 'lock_version',
    ];

    protected $hidden = [
        'microchip_number',
        'critical_allergies',
        'chronic_conditions',
        'emergency_notes',
        'primary_clinic_contact',
        'emergency_contact',
    ];

    protected $attributes = [
        'status' => 'active',
        'privacy' => 'private',
        'timezone' => 'Europe/Vilnius',
        'microchip_status' => 'unknown',
        'reproductive_status' => 'unknown',
        'birth_date_estimated' => false,
        'lock_version' => 1,
        'allergy_knowledge_status' => 'unknown',
        'medication_knowledge_status' => 'unknown',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'birth_date_estimated' => 'boolean',
            'microchip_number' => 'encrypted',
            'microchip_checked_on' => 'date',
            'allergy_knowledge_status' => MedicalKnowledgeStatus::class,
            'critical_allergies' => 'encrypted:array',
            'medication_knowledge_status' => MedicalKnowledgeStatus::class,
            'chronic_conditions' => 'encrypted:array',
            'emergency_notes' => 'encrypted',
            'primary_clinic_contact' => 'encrypted',
            'emergency_contact' => 'encrypted:array',
            'last_visit_at' => 'datetime',
            'next_appointment_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    /** @return HasMany<\App\Models\MedicalEvent, $this>*/
    public function events(): HasMany
    {
        return $this->hasMany(MedicalEvent::class);
    }

    /** @return HasMany<\App\Models\Vaccination, $this>*/
    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    /** @return HasMany<\App\Models\WeightEntry, $this>*/
    public function weightEntries(): HasMany
    {
        return $this->hasMany(WeightEntry::class);
    }

    /** @return HasMany<\App\Models\Medication, $this>*/
    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    /** @return HasMany<\App\Models\MedicationDose, $this>*/
    public function doses(): HasMany
    {
        return $this->hasMany(MedicationDose::class);
    }

    /** @return HasMany<\App\Models\MedicalDocument, $this>*/
    public function documents(): HasMany
    {
        return $this->hasMany(MedicalDocument::class);
    }

    /** @return HasMany<\App\Models\MedicalReminder, $this>*/
    public function reminders(): HasMany
    {
        return $this->hasMany(MedicalReminder::class);
    }

    /** @return HasMany<\App\Models\MedicalAccessGrant, $this>*/
    public function accessGrants(): HasMany
    {
        return $this->hasMany(MedicalAccessGrant::class);
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function petProfile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function scopeForOwnerDirectory(Builder $query, string $ownerKey): Builder
    {
        return $query
            ->select([
                'id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
                'species', 'breed', 'birth_date', 'birth_date_estimated',
                'current_weight_grams', 'image_url', 'status', 'privacy',
                'last_visit_at', 'next_appointment_at', 'updated_at',
            ])
            ->where('owner_key', $ownerKey)
            ->where('status', 'active');
    }

    public function scopeAccessibleTo(
        Builder $query,
        User $user,
        PetProfilePermission $permission = PetProfilePermission::ViewMedical,
    ): Builder {
        $roles = collect(PetManagerRole::cases())
            ->filter(fn (PetManagerRole $role): bool => in_array(
                $permission,
                $role->defaultPermissions(),
                true,
            ))
            ->map(fn (PetManagerRole $role): string => $role->value)
            ->all();

        return $query->where(function (Builder $access) use ($permission, $roles, $user): void {
            $access
                ->where(function (Builder $legacy) use ($user): void {
                    $legacy
                        ->whereNull('pet_profile_id')
                        ->where(function (Builder $legacyOwner) use ($user): void {
                            $legacyOwner
                                ->where('owner_id', $user->id)
                                ->orWhere('owner_key', $user->actor_key);
                        });
                })
                ->orWhereHas('petProfile', function (Builder $profile) use ($user): void {
                    $profile->where('user_id', $user->id);
                })
                ->orWhereHas('petProfile.managers', function (Builder $managers) use ($permission, $roles, $user): void {
                    $managers->where('user_id', $user->id);
                    PetProfileManager::constrainActiveAt($managers, now());
                    $managers->where(function (Builder $roleOrOverride) use ($permission, $roles): void {
                        $roleOrOverride
                            ->where(function (Builder $role) use ($permission, $roles): void {
                                $role
                                    ->whereIn('role', $roles)
                                    ->where(function (Builder $notDenied) use ($permission): void {
                                        $notDenied
                                            ->whereNull('permission_overrides')
                                            ->orWhereJsonDoesntContain(
                                                'permission_overrides->deny',
                                                $permission->value,
                                            );
                                    });
                            })
                            ->orWhereJsonContains(
                                'permission_overrides->grant',
                                $permission->value,
                            );
                    });
                });
        });
    }

    public function isOwnedBy(string $actorKey): bool
    {
        return $this->pet_profile_id === null
            && hash_equals($this->owner_key, $actorKey);
    }

    public function maskedMicrochip(): ?string
    {
        $number = preg_replace('/\s+/', '', (string) $this->microchip_number);

        if ($number === '') {
            return null;
        }

        return str_repeat('*', max(0, mb_strlen($number) - 4)).mb_substr($number, -4);
    }
}
