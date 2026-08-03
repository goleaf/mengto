<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetBirthDatePrecision;
use App\Models\PetProfile;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final readonly class PetProfileAgeLabel
{
    public function __construct(
        private LocaleFormatter $formatter,
        private PetProfileAgeCalculator $calculator,
    ) {}

    public function for(PetProfile $profile, ?DateTimeInterface $at = null): ?string
    {
        $range = $this->calculator->monthsRange($profile, $at);

        if ($range === null) {
            return null;
        }

        return match ($profile->birth_date_precision) {
            PetBirthDatePrecision::Exact => $this->formatMonths($range['minimum']),
            PetBirthDatePrecision::Estimated,
            PetBirthDatePrecision::Month => $this->approximate(
                $this->formatMonths((int) floor(($range['minimum'] + $range['maximum']) / 2)),
            ),
            PetBirthDatePrecision::Year => $this->fromRange($range),
            PetBirthDatePrecision::AgeEstimate => $this->approximate(
                $this->formatMonths($range['minimum']),
            ),
            PetBirthDatePrecision::Unknown => null,
        };
    }

    public function celebrationFor(PetProfile $profile): ?string
    {
        if ($profile->birthday_celebration_month === null
            || $profile->birthday_celebration_day === null) {
            return null;
        }

        $date = CarbonImmutable::create(
            2000,
            $profile->birthday_celebration_month,
            $profile->birthday_celebration_day,
        );

        return $this->formatter->monthDay($date);
    }

    /** @param array{minimum: int, maximum: int} $range */
    private function fromRange(array $range): string
    {
        $minimumYears = intdiv($range['minimum'], 12);
        $maximumYears = intdiv($range['maximum'], 12);

        if ($minimumYears === $maximumYears) {
            return $this->approximate($this->formatYears($maximumYears));
        }

        return trans_choice('pet_profiles.public.age_year_range', $maximumYears, [
            'minimum' => $this->formatter->number($minimumYears),
            'maximum' => $this->formatter->number($maximumYears),
        ]);
    }

    private function formatMonths(int $months): string
    {
        $years = intdiv($months, 12);
        $remainingMonths = $months % 12;

        if ($years === 0) {
            return trans_choice('pet_profiles.public.age_months', $remainingMonths, [
                'count' => $this->formatter->number($remainingMonths),
            ]);
        }

        $yearLabel = $this->formatYears($years);

        if ($remainingMonths === 0) {
            return $yearLabel;
        }

        return $this->formatter->list([
            $yearLabel,
            trans_choice('pet_profiles.public.age_months', $remainingMonths, [
                'count' => $this->formatter->number($remainingMonths),
            ]),
        ]);
    }

    private function formatYears(int $years): string
    {
        return trans_choice('pet_profiles.public.age_years', $years, [
            'count' => $this->formatter->number($years),
        ]);
    }

    private function approximate(?string $age): ?string
    {
        return $age === null
            ? null
            : __('pet_profiles.public.approximately_age', ['age' => $age]);
    }
}
