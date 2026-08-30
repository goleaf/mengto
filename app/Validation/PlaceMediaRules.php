<?php

declare(strict_types=1);

namespace App\Validation;

final class PlaceMediaRules
{
    /** @return list<string> */
    public static function upload(): array
    {
        return [
            'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'mimetypes:image/jpeg,image/png,image/webp',
            'max:5120',
            'dimensions:min_width=32,min_height=32,max_width=12000,max_height=12000',
        ];
    }
}
