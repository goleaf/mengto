<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetProfileVisibility;
use Database\Factories\PetProfilePrivacySettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property bool $allow_direct_link
 * @property bool $allow_external_indexing
 * @property bool $is_discoverable
 * @property string $manager_display_mode
 * @property string $owner_display_mode
 * @property PetProfileVisibility $profile_visibility
 * @property string $public_location_precision
 * @property array<string, string>|null $section_rules
 */
final class PetProfilePrivacySetting extends Model
{
    /** @use HasFactory<PetProfilePrivacySettingFactory> */
    use HasFactory;

    protected $fillable = [
        'pet_profile_id',
        'profile_visibility',
        'section_rules',
        'is_discoverable',
        'allow_external_indexing',
        'allow_direct_link',
        'owner_display_mode',
        'manager_display_mode',
        'public_location_precision',
        'lock_version',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'profile_visibility' => PetProfileVisibility::class,
            'section_rules' => 'array',
            'is_discoverable' => 'boolean',
            'allow_external_indexing' => 'boolean',
            'allow_direct_link' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function visibilityFor(string $section): PetProfileVisibility
    {
        $value = $this->section_rules[$section] ?? null;

        return is_string($value)
            ? PetProfileVisibility::tryFrom($value) ?? $this->profile_visibility
            : $this->profile_visibility;
    }
}
