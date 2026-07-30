<?php

namespace App\Models;

use App\Enums\SearchVolunteerStatus;
use Database\Factories\SearchVolunteerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchVolunteer extends Model
{
    /** @use HasFactory<SearchVolunteerFactory> */
    use HasFactory;

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

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }
}
