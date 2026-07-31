<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PhotoAssetFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property int $id
 * @property string $key
 * @property int $position
 * @property string $post_key
 * @property-read Collection<int, PhotoComment> $comments
 * @property-read Collection<int, PhotoReaction> $reactions
 * @property Carbon|null $updated_at
 */
final class PhotoAsset extends Model
{
    /** @use HasFactory<PhotoAssetFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'post_key',
        'position',
    ];

    protected function casts(): array
    {
        return ['position' => 'int'];
    }

    /** @return HasMany<PhotoComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(PhotoComment::class);
    }

    /** @return HasMany<PhotoReaction, $this> */
    public function reactions(): HasMany
    {
        return $this->hasMany(PhotoReaction::class);
    }
}
