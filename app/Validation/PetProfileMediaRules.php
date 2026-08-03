<?php

declare(strict_types=1);

namespace App\Validation;

final class PetProfileMediaRules
{
    /** @return list<string> */
    public static function upload(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'mimetypes:image/jpeg,image/png,image/webp',
            'max:5120',
            'dimensions:min_width=32,min_height=32,max_width=12000,max_height=12000',
        ];
    }

    /** @return list<string> */
    public static function altText(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'min:2', 'max:500'];
    }

    /** @return list<string> */
    public static function idempotencyKey(): array
    {
        return ['required', 'string', 'min:16', 'max:190'];
    }
}
