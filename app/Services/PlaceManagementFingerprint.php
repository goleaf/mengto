<?php

declare(strict_types=1);

namespace App\Services;

final class PlaceManagementFingerprint
{
    /** @param array<string, mixed> $payload */
    public function make(array $payload): string
    {
        $encoded = json_encode($this->normalize($payload), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $key = (string) config('app.key');

        return hash_hmac('sha256', $encoded, $key !== '' ? $key : 'place-management-local-key');
    }

    private function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            $normalized = array_map(fn (mixed $item): mixed => $this->normalize($item), $value);
            sort($normalized);

            return $normalized;
        }

        ksort($value);

        return array_map(fn (mixed $item): mixed => $this->normalize($item), $value);
    }
}
