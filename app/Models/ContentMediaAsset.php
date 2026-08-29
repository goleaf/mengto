<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentMediaStatus;
use App\Enums\ContentMediaType;
use Database\Factories\ContentMediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ContentMediaAsset extends Model
{
    /** @use HasFactory<ContentMediaAssetFactory> */
    use HasFactory;

    protected $fillable = [
        'media_key',
        'owner_user_id',
        'created_by_user_id',
        'media_type',
        'status',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'byte_size',
        'checksum_sha256',
        'alt_text',
        'licence',
        'safe_metadata',
        'retained_until',
    ];

    protected $hidden = ['disk', 'path', 'original_name', 'checksum_sha256'];

    protected function casts(): array
    {
        return [
            'media_type' => ContentMediaType::class,
            'status' => ContentMediaStatus::class,
            'byte_size' => 'integer',
            'safe_metadata' => 'array',
            'retained_until' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsToMany<ContentPublication, $this> */
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(
            ContentPublication::class,
            'content_publication_media',
        )->withPivot(['position', 'is_cover', 'caption'])->withTimestamps();
    }

    /** @return HasMany<PetProfileMedia, $this> */
    public function petProfileMedia(): HasMany
    {
        return $this->hasMany(PetProfileMedia::class);
    }
}
