<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DisputeStatus;
use Database\Factories\OrderDisputeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property string $details
 * @property array<array-key, mixed>|null $evidence
 * @property int $id
 * @property-read Listing|null $listing
 * @property int $listing_id
 * @property string $opened_by_key
 * @property string $opened_by_role
 * @property-read Order|null $order
 * @property int $order_id
 * @property string $priority
 * @property string $reason
 * @property string|null $resolution
 * @property Carbon|null $resolved_at
 * @property DisputeStatus $status
 * @property Carbon|null $updated_at
 */
class OrderDispute extends Model
{
    /** @use HasFactory<OrderDisputeFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id', 'listing_id', 'opened_by_key', 'opened_by_role', 'reason',
        'details', 'evidence', 'priority', 'status', 'resolution',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DisputeStatus::class,
            'evidence' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\Order, $this>*/
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<\App\Models\Listing, $this>*/
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
