<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceMediaVariant as PlaceMediaVariantType;
use App\Enums\PlaceMediaVariantStatus;
use Database\Factories\PlaceMediaVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceMediaVariant extends Model
{
    /** @use HasFactory<PlaceMediaVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'place_media_id', 'variant', 'status', 'disk', 'path', 'mime_type',
        'width', 'height', 'byte_size', 'checksum_sha256', 'failure_code',
        'generated_at',
    ];

    protected $hidden = ['disk', 'path', 'checksum_sha256', 'failure_code'];

    protected function casts(): array
    {
        return [
            'variant' => PlaceMediaVariantType::class,
            'status' => PlaceMediaVariantStatus::class,
            'width' => 'integer',
            'height' => 'integer',
            'byte_size' => 'integer',
            'generated_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PlaceMedia, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(PlaceMedia::class, 'place_media_id');
    }
}
