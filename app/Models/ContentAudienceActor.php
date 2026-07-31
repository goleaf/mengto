<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentAudienceActorEffect;
use Database\Factories\ContentAudienceActorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentAudienceActor extends Model
{
    /** @use HasFactory<ContentAudienceActorFactory> */
    use HasFactory;

    protected $fillable = [
        'content_audience_rule_id',
        'social_actor_id',
        'effect',
    ];

    protected function casts(): array
    {
        return ['effect' => ContentAudienceActorEffect::class];
    }

    /** @return BelongsTo<ContentAudienceRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(ContentAudienceRule::class, 'content_audience_rule_id');
    }

    /** @return BelongsTo<SocialActor, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'social_actor_id');
    }
}
