<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class ApproximateMeetupLocation implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $containsCoordinatePair = preg_match(
            '/(?<!\d)[+-]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?)[,;\s]+[+-]?(?:1[0-7]\d(?:\.\d+)?|\d?\d(?:\.\d+)?|180(?:\.0+)?)(?!\d)/u',
            $value,
        ) === 1;
        $containsStreetAddress = preg_match(
            '/(?:\b\d{1,5}[a-z]?\s+(?:[\p{L}\p{M}.\-]+\s*){0,4}(?:street|st\.?|avenue|ave\.?|road|rd\.?|lane|boulevard|blvd\.?|gatvė|g\.?|prospektas|pr\.?|alėja|улица|ул\.?|дом|шоссе|переулок)\b|\b(?:street|avenue|road|lane|boulevard|gatvė|prospektas|alėja|улица|дом|шоссе|переулок)\b[^,;\n]{0,80}\b\d{1,5}[a-z]?\b)/iu',
            $value,
        ) === 1;

        if ($containsCoordinatePair || $containsStreetAddress) {
            $fail('forum_events.validation.approximate_location')->translate();
        }
    }
}
