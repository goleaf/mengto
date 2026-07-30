<?php

namespace App\Models;

use Database\Factories\MedicalRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    /** @use HasFactory<MedicalRecordFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
        'species', 'breed', 'birth_date', 'birth_date_estimated', 'sex',
        'reproductive_status', 'current_weight_grams', 'image_url', 'status',
        'privacy', 'timezone', 'microchip_status', 'microchip_number',
        'microchip_checked_on', 'blood_group', 'critical_allergies',
        'chronic_conditions', 'emergency_notes', 'primary_clinic_name',
        'primary_clinic_contact', 'emergency_contact', 'last_visit_at',
        'next_appointment_at', 'lock_version', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
        'species', 'breed', 'birth_date', 'birth_date_estimated', 'sex',
        'reproductive_status', 'current_weight_grams', 'image_url', 'status',
        'privacy', 'timezone', 'microchip_status', 'microchip_number',
        'microchip_checked_on', 'blood_group', 'critical_allergies',
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
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'birth_date_estimated' => 'boolean',
            'microchip_number' => 'encrypted',
            'microchip_checked_on' => 'date',
            'critical_allergies' => 'encrypted:array',
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

    public function events(): HasMany
    {
        return $this->hasMany(MedicalEvent::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    public function weightEntries(): HasMany
    {
        return $this->hasMany(WeightEntry::class);
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    public function doses(): HasMany
    {
        return $this->hasMany(MedicationDose::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MedicalDocument::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(MedicalReminder::class);
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(MedicalAccessGrant::class);
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

    public function isOwnedBy(string $actorKey): bool
    {
        return hash_equals($this->owner_key, $actorKey);
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
