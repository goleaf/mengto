<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DirectoryFilter
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, array<int, string>>  $filterTerms
     * @param  array<int, string>  $searchFields
     * @return array<int, array<string, mixed>>
     */
    public function apply(
        array $items,
        ?string $query,
        ?string $filter,
        ?string $sort,
        array $filterTerms,
        array $searchFields,
    ): array {
        $normalizedQuery = Str::lower(trim((string) $query));
        $normalizedFilter = Str::lower(trim((string) $filter));

        $filtered = array_filter($items, function (array $item) use ($normalizedQuery, $normalizedFilter, $filterTerms, $searchFields): bool {
            $searchable = Str::lower(implode(' ', array_map(
                static fn (mixed $value): string => is_array($value) ? implode(' ', $value) : (string) $value,
                Arr::only($item, $searchFields),
            )));

            if ($normalizedQuery !== '' && ! Str::contains($searchable, $normalizedQuery)) {
                return false;
            }

            if ($normalizedFilter === '' || ! isset($filterTerms[$normalizedFilter])) {
                return true;
            }

            return Str::contains($searchable, $filterTerms[$normalizedFilter]);
        });

        $sorted = array_values($filtered);

        $comparison = match ($sort) {
            'name' => static fn (array $left, array $right): int => strcasecmp(
                (string) ($left['name'] ?? $left['title'] ?? ''),
                (string) ($right['name'] ?? $right['title'] ?? ''),
            ),
            'active' => fn (array $left, array $right): int => $this->activityScore($right)
                <=> $this->activityScore($left),
            'soonest' => static fn (array $left, array $right): int => strcmp(
                (string) ($left['datetime'] ?? ''),
                (string) ($right['datetime'] ?? ''),
            ),
            'closest' => static fn (array $left, array $right): int => (float) ($left['distance'] ?? 999)
                <=> (float) ($right['distance'] ?? 999),
            default => null,
        };

        if ($comparison !== null) {
            usort($sorted, $comparison);
        }

        return $sorted;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function activityScore(array $item): float
    {
        $value = (string) ($item['activity'] ?? $item['members'] ?? '0');

        if (! preg_match('/([\d.]+)\s*(k)?/i', $value, $matches)) {
            return 0;
        }

        $score = (float) $matches[1];

        return isset($matches[2]) && Str::lower($matches[2]) === 'k'
            ? $score * 1000
            : $score;
    }
}
