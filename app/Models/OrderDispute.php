<?php

namespace App\Models;

use App\Enums\DisputeStatus;
use Database\Factories\OrderDisputeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
