<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SearchContactRelayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SearchContactRelay extends Model
{
    /** @use HasFactory<SearchContactRelayFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id',
        'sender_user_id',
        'recipient_user_id',
        'idempotency_key',
        'purpose',
        'message',
        'status',
        'read_at',
    ];

    protected $hidden = ['message'];

    protected function casts(): array
    {
        return [
            'message' => 'encrypted',
            'read_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<SearchCase, $this> */
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
