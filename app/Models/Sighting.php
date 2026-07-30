<?php

namespace App\Models;

use App\Enums\SightingStatus;
use Database\Factories\SightingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sighting extends Model
{
    /** @use HasFactory<SightingFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'reporter_id', 'reporter_key', 'reporter_name',
        'idempotency_key', 'status', 'observed_at', 'submitted_at',
        'time_accuracy', 'public_area', 'public_latitude', 'public_longitude',
        'exact_location', 'direction', 'distance', 'confidence',
        'contact_status', 'animal_condition', 'danger', 'notes', 'photo_url',
        'video_url', 'is_anonymous', 'exact_location_public', 'risk_flags',
        'verified_by_key', 'verified_at',
    ];

    protected $hidden = ['exact_location'];

    protected $attributes = [
        'status' => 'submitted',
        'time_accuracy' => 'exact',
        'confidence' => 'possible',
        'contact_status' => 'seen-only',
        'is_anonymous' => false,
        'exact_location_public' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => SightingStatus::class,
            'observed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'public_latitude' => 'decimal:6',
            'public_longitude' => 'decimal:6',
            'exact_location' => 'encrypted:array',
            'is_anonymous' => 'boolean',
            'exact_location_public' => 'boolean',
            'risk_flags' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(SearchReport::class);
    }
}
