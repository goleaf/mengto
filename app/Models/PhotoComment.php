<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PhotoCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $body
 * @property Carbon|null $created_at
 * @property int $id
 * @property string $idempotency_key
 * @property int $photo_asset_id
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property-read PhotoAsset $photoAsset
 * @property-read User $user
 */
final class PhotoComment extends Model
{
    /** @use HasFactory<PhotoCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'photo_asset_id',
        'user_id',
        'body',
        'idempotency_key',
    ];

    protected $hidden = ['idempotency_key'];

    /** @return BelongsTo<PhotoAsset, $this> */
    public function photoAsset(): BelongsTo
    {
        return $this->belongsTo(PhotoAsset::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
