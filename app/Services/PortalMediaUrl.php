<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class PortalMediaUrl
{
    public function for(string $pathOrUrl): string
    {
        $value = trim($pathOrUrl);

        if ($value === '') {
            return '';
        }

        $urlPath = parse_url($value, PHP_URL_PATH);

        if (is_string($urlPath) && Str::startsWith($urlPath, '/portal-media/')) {
            return $value;
        }

        if (is_string($urlPath) && Str::startsWith($urlPath, '/storage/')) {
            $value = Str::after($urlPath, '/storage/');
        } elseif (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return route('portal-media.show', ['path' => ltrim($value, '/')]);
    }
}
