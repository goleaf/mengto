<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SearchVolunteerStatus;
use Database\Factories\SearchVolunteerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $actor_key
 * @property Carbon|null $available_until
 * @property array<array-key, mixed>|null $capabilities
 * @property Carbon|null $created_at
 * @property string $display_name
 * @property int $id
 * @property Carbon $joined_at
 * @property Carbon|null $last_check_in_at
 * @property Carbon|null $location_expires_at
 * @property string $privacy_level
 * @property string $role
 * @property-read SearchCase|null $searchCase
 * @property int $search_case_id
 * @property SearchVolunteerStatus $status
 * @property array<array-key, mixed>|null $temporary_location
 * @property Carbon|null $updated_at
 */
class SearchVolunteer extends Model
{
    /** @use HasFactory<SearchVolunteerFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn (): bool => SearchCase::invalidateDirectoryStats());
        static::deleted(fn (): bool => SearchCase::invalidateDirectoryStats());
    }

    protected $fillable = [
        'search_case_id', 'actor_key', 'display_name', 'role', 'capabilities',
        'status', 'privacy_level', 'available_until', 'joined_at',
        'last_check_in_at', 'temporary_location', 'location_expires_at',
    ];

    protected $hidden = ['temporary_location'];

    protected $attributes = [
        'role' => 'volunteer',
        'status' => 'active',
        'privacy_level' => 'team-only',
    ];

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'status' => SearchVolunteerStatus::class,
            'available_until' => 'datetime',
            'joined_at' => 'datetime',
            'last_check_in_at' => 'datetime',
            'temporary_location' => 'encrypted:array',
            'location_expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }
}
