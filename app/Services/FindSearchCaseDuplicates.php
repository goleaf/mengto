<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SearchCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class FindSearchCaseDuplicates
{
    /**
     * @param  array<string, mixed>  $candidate
     * @return Collection<int, SearchCase>
     */
    public function handle(array $candidate, ?int $excludeCaseId = null): Collection
    {
        $species = trim((string) ($candidate['species'] ?? ''));
        $city = trim((string) ($candidate['city'] ?? ''));

        if ($species === '' && $city === '') {
            return collect();
        }

        return SearchCase::query()
            ->select([
                'id',
                'slug',
                'public_code',
                'type',
                'status',
                'pet_name',
                'species',
                'breed',
                'primary_color',
                'distinctive_marks',
                'last_seen_area',
                'city',
                'last_seen_at',
            ])
            ->publiclyVisible()
            ->whereNull('archived_at')
            ->where(static function ($query) use ($city, $species): void {
                if ($species !== '' && $city !== '') {
                    $query
                        ->where('species', $species)
                        ->orWhere('city', $city);

                    return;
                }

                if ($species !== '') {
                    $query->where('species', $species);
                }

                if ($city !== '') {
                    $query->where('city', $city);
                }
            })
            ->when($excludeCaseId !== null, fn ($query) => $query->whereKeyNot($excludeCaseId))
            ->latest('last_seen_at')
            ->limit(100)
            ->get()
            ->map(fn (SearchCase $searchCase): array => [
                'case' => $searchCase,
                'score' => $this->score($searchCase, $candidate),
            ])
            ->filter(fn (array $match): bool => $match['score'] >= 5)
            ->sortByDesc('score')
            ->take(5)
            ->pluck('case')
            ->values();
    }

    /** @param array<string, mixed> $candidate */
    private function score(SearchCase $searchCase, array $candidate): int
    {
        $score = 0;
        $score += $this->same($searchCase->species, $candidate['species'] ?? null) ? 3 : 0;
        $score += $this->same($searchCase->city, $candidate['city'] ?? null) ? 2 : 0;
        $score += $this->same($searchCase->breed, $candidate['breed'] ?? null) ? 2 : 0;
        $score += $this->same($searchCase->primary_color, $candidate['primary_color'] ?? null) ? 2 : 0;
        $score += $this->overlaps($searchCase->last_seen_area, $candidate['last_seen_area'] ?? null) ? 2 : 0;
        $score += $this->overlaps($searchCase->distinctive_marks, $candidate['distinctive_marks'] ?? null) ? 1 : 0;

        $candidateDate = filled($candidate['last_seen_at'] ?? null)
            ? Carbon::parse((string) $candidate['last_seen_at'])
            : null;

        if ($candidateDate !== null && $searchCase->last_seen_at->diffInDays($candidateDate) <= 14) {
            $score++;
        }

        return $score;
    }

    private function same(?string $left, mixed $right): bool
    {
        return filled($left)
            && filled($right)
            && Str::lower(trim((string) $left)) === Str::lower(trim((string) $right));
    }

    private function overlaps(?string $left, mixed $right): bool
    {
        if (blank($left) || blank($right)) {
            return false;
        }

        $leftWords = Str::of((string) $left)->lower()->words(12, '')->explode(' ');
        $rightWords = Str::of((string) $right)->lower()->words(12, '')->explode(' ');

        return $leftWords->intersect($rightWords)
            ->filter(fn (string $word): bool => mb_strlen($word) >= 4)
            ->count() >= 2;
    }
}
