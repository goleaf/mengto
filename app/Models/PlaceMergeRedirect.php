<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceVisibility;
use Database\Factories\PlaceMergeRedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceMergeRedirect extends Model
{
    /** @use HasFactory<PlaceMergeRedirectFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'source_place_id',
        'destination_place_id',
        'place_submission_event_id',
        'created_by_user_id',
        'restored_by_user_id',
        'source_identifier',
        'source_visibility',
        'restored_at',
        'created_at',
    ];

    protected $hidden = [
        'source_place_id',
        'destination_place_id',
        'place_submission_event_id',
        'source_identifier',
        'source_visibility',
    ];

    protected function casts(): array
    {
        return [
            'source_visibility' => PlaceVisibility::class,
            'restored_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Place, $this> */
    public function sourcePlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'source_place_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function destinationPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'destination_place_id');
    }

    /** @return BelongsTo<PlaceSubmissionEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmissionEvent::class, 'place_submission_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by_user_id');
    }
}
