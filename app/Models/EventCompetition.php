<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventCompetitionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EventCompetition extends Model
{
    use HasFactory;
    protected $table = 'forum_event_competitions';
    protected $guarded = [];
    protected function casts(): array { return ['status' => EventCompetitionStatus::class, 'judging_opens_at' => 'immutable_datetime', 'judging_closes_at' => 'immutable_datetime', 'finalized_at' => 'immutable_datetime', 'published_at' => 'immutable_datetime']; }
    public function event(): BelongsTo { return $this->belongsTo(ForumEvent::class, 'forum_event_id'); }
    public function ruleVersions(): HasMany { return $this->hasMany(EventCompetitionRuleVersion::class, 'competition_id'); }
    public function categories(): HasMany { return $this->hasMany(EventCompetitionCategory::class, 'competition_id'); }
    public function entries(): HasMany { return $this->hasMany(EventCompetitionEntry::class, 'competition_id'); }
    public function judgeAssignments(): HasMany { return $this->hasMany(EventCompetitionJudgeAssignment::class, 'competition_id'); }
    public function resultVersions(): HasMany { return $this->hasMany(EventCompetitionResultVersion::class, 'competition_id'); }
    public function histories(): HasMany { return $this->hasMany(EventCompetitionHistory::class, 'competition_id'); }
}
