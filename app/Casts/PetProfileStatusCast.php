<?php

declare(strict_types=1);

namespace App\Casts;

use App\Enums\PetProfileStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** @implements CastsAttributes<PetProfileStatus, PetProfileStatus|string> */
final class PetProfileStatusCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): PetProfileStatus
    {
        return PetProfileStatus::from($this->normalize((string) $value));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof PetProfileStatus) {
            return $value->value;
        }

        return PetProfileStatus::from($this->normalize((string) $value))->value;
    }

    private function normalize(string $value): string
    {
        return $value === 'inactive'
            ? PetProfileStatus::Archived->value
            : $value;
    }
}
