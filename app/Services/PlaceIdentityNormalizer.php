<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class PlaceIdentityNormalizer
{
    public function name(string $value): string
    {
        return $this->words($value);
    }

    public function address(?string $value): ?string
    {
        return filled($value) ? $this->words((string) $value) : null;
    }

    public function phone(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return is_string($digits) && $digits !== '' ? $digits : null;
    }

    public function email(?string $value): ?string
    {
        return filled($value) ? Str::lower(trim((string) $value)) : null;
    }

    public function website(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $parts = parse_url(trim((string) $value));

        if (! is_array($parts) || ! isset($parts['host'])) {
            return null;
        }

        $host = Str::lower((string) $parts['host']);
        $host = Str::startsWith($host, 'www.') ? Str::after($host, 'www.') : $host;
        $path = isset($parts['path']) ? '/'.trim((string) $parts['path'], '/') : '';
        $query = [];

        if (isset($parts['query'])) {
            parse_str((string) $parts['query'], $query);
            $query = array_filter(
                $query,
                static fn (string $key): bool => ! Str::startsWith(Str::lower($key), ['utm_', 'fbclid', 'gclid']),
                ARRAY_FILTER_USE_KEY,
            );
            ksort($query);
        }

        return $host.($path === '/' ? '' : $path).($query === [] ? '' : '?'.http_build_query($query));
    }

    public function distanceMeters(
        string|float|int|null $firstLatitude,
        string|float|int|null $firstLongitude,
        string|float|int|null $secondLatitude,
        string|float|int|null $secondLongitude,
    ): ?int {
        $coordinates = [
            $firstLatitude,
            $firstLongitude,
            $secondLatitude,
            $secondLongitude,
        ];

        if (collect($coordinates)->contains(static fn (mixed $value): bool => ! is_numeric($value))) {
            return null;
        }

        [$lat1, $lon1, $lat2, $lon2] = array_map(
            static fn (mixed $value): float => (float) $value,
            $coordinates,
        );

        if (abs($lat1) > 90 || abs($lat2) > 90 || abs($lon1) > 180 || abs($lon2) > 180) {
            return null;
        }

        $latitudeDelta = deg2rad($lat2 - $lat1);
        $longitudeDelta = deg2rad($lon2 - $lon1);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($longitudeDelta / 2) ** 2;
        $a = min(1.0, max(0.0, $a));

        return (int) round(6_371_000 * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function words(string $value): string
    {
        $ascii = Str::lower(Str::ascii(trim($value)));
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $ascii);

        return trim((string) preg_replace('/\s+/', ' ', (string) $normalized));
    }
}
