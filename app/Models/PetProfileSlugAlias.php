<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PetProfileSlugAliasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PetProfileSlugAlias extends Model
{
    /** @use HasFactory<PetProfileSlugAliasFactory> */
    use HasFactory;

    protected $fillable = [
        'pet_profile_id',
        'slug',
        'source',
        'is_active',
        'retired_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'retired_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }
}
