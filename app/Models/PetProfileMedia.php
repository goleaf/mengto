<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetProfileMediaStatus;
use Database\Factories\PetProfileMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read ContentMediaAsset $asset
 * @property int $content_media_asset_id
 * @property string|null $current_key
 * @property int $id
 * @property string $media_key
 * @property int $pet_profile_id
 * @property Carbon|null $recoverable_until
 * @property PetProfileMediaStatus $status
 */
final class PetProfileMedia extends Model
{
    /** @use HasFactory<PetProfileMediaFactory> */
    use HasFactory;

    protected $table = 'pet_profile_media';

    protected $fillable = [
        'media_key',
        'pet_profile_id',
        'content_media_asset_id',
        'attached_by_user_id',
        'role',
        'status',
        'current_key',
        'upload_key',
        'recoverable_until',
        'replaced_at',
        'removed_at',
        'restored_at',
    ];

    protected $hidden = ['current_key', 'upload_key'];

    protected function casts(): array
    {
        return [
            'status' => PetProfileMediaStatus::class,
            'recoverable_until' => 'immutable_datetime',
            'replaced_at' => 'immutable_datetime',
            'removed_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'media_key';
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<ContentMediaAsset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(ContentMediaAsset::class, 'content_media_asset_id');
    }

    /** @return BelongsTo<User, $this> */
    public function attachedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attached_by_user_id');
    }
}
