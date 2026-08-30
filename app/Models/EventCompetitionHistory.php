<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class EventCompetitionHistory extends Model
{
    public const UPDATED_AT = null; protected $table = 'forum_event_competition_history'; protected $guarded = [];
    protected function casts(): array { return ['metadata' => 'array', 'created_at' => 'immutable_datetime']; }
}
