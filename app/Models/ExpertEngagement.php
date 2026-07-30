<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExpertEngagementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property int $id
 * @property bool $is_saved
 * @property bool $is_subscribed
 * @property Carbon|null $last_viewed_at
 * @property Carbon|null $updated_at
 * @property string $user_key
 */
class ExpertEngagement extends Model
{
    /** @use HasFactory<ExpertEngagementFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'user_key', 'is_saved', 'is_subscribed',
        'last_viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_saved' => 'boolean',
            'is_subscribed' => 'boolean',
            'last_viewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }
}
