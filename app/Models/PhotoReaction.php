<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PhotoReactionType;
use Database\Factories\PhotoReactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property int $id
 * @property int $photo_asset_id
 * @property PhotoReactionType $reaction
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property-read PhotoAsset $photoAsset
 * @property-read User $user
 */
final class PhotoReaction extends Model
{
    /** @use HasFactory<PhotoReactionFactory> */
    use HasFactory;

    protected $fillable = [
        'photo_asset_id',
        'user_id',
        'reaction',
    ];

    protected function casts(): array
    {
        return ['reaction' => PhotoReactionType::class];
    }

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
