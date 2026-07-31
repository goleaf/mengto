<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConfirmationState;
use Carbon\CarbonImmutable;
use Database\Factories\ForumConfirmationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $abstentions
 * @property float|string $confidence
 * @property CarbonImmutable|null $decided_at
 * @property CarbonImmutable|null $expires_at
 * @property int $id
 * @property array<string, mixed>|null $metadata
 * @property int $opposing_votes
 * @property int $requester_user_id
 * @property int $required_diversity
 * @property int $required_quorum
 * @property string $risk_class
 * @property ConfirmationState $state
 * @property int $supporting_votes
 */
final class ForumConfirmation extends Model
{
    /** @use HasFactory<ForumConfirmationFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'requester_user_id',
        'state',
        'claim_text',
        'structured_claim',
        'scope',
        'risk_class',
        'required_quorum',
        'required_diversity',
        'confidence',
        'supporting_votes',
        'opposing_votes',
        'abstentions',
        'moderator_user_id',
        'moderator_decision',
        'review_deadline_at',
        'expires_at',
        'revalidation_due_at',
        'decided_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'state' => ConfirmationState::class,
            'structured_claim' => 'array',
            'scope' => 'array',
            'required_quorum' => 'integer',
            'required_diversity' => 'integer',
            'confidence' => 'decimal:4',
            'supporting_votes' => 'integer',
            'opposing_votes' => 'integer',
            'abstentions' => 'integer',
            'review_deadline_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'revalidation_due_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    /** @return HasMany<ForumConfirmationVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(ForumConfirmationVote::class);
    }

    /** @return HasMany<ForumConfirmationEvidence, $this> */
    public function evidence(): HasMany
    {
        return $this->hasMany(ForumConfirmationEvidence::class);
    }
}
