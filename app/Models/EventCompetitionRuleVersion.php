<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EventCompetitionRuleVersion extends Model
{
    public const UPDATED_AT = null;
    protected $table = 'forum_event_competition_rule_versions'; protected $guarded = [];
    protected function casts(): array { return ['is_material' => 'boolean', 'created_at' => 'immutable_datetime']; }
    public function competition(): BelongsTo { return $this->belongsTo(EventCompetition::class, 'competition_id'); }
}
