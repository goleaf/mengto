<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceWarningEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property CarbonImmutable $created_at */
final class PlaceWarningEvent extends Model
{
    /** @use HasFactory<PlaceWarningEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'place_warning_id', 'actor_user_id', 'idempotency_key', 'event_type', 'from_status', 'to_status',
        'public_summary_key', 'private_note', 'metadata', 'created_at',
    ];

    protected $hidden = ['private_note', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'created_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceWarning, $this> */
    public function warning(): BelongsTo
    {
        return $this->belongsTo(PlaceWarning::class, 'place_warning_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
