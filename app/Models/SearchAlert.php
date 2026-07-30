<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SearchAlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property array<array-key, mixed> $audiences
 * @property array<array-key, mixed> $channels
 * @property Carbon|null $created_at
 * @property int $id
 * @property string $kind
 * @property string $message
 * @property int $radius_km
 * @property int $recipient_count
 * @property string $region
 * @property-read SearchCase|null $searchCase
 * @property int $search_case_id
 * @property Carbon|null $sent_at
 * @property string $status
 * @property Carbon|null $stopped_at
 * @property Carbon|null $updated_at
 */
class SearchAlert extends Model
{
    /** @use HasFactory<SearchAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'kind', 'radius_km', 'region', 'channels',
        'audiences', 'status', 'recipient_count', 'message', 'sent_at',
        'stopped_at',
    ];

    protected $attributes = [
        'status' => 'queued',
        'recipient_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'audiences' => 'array',
            'sent_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }
}
