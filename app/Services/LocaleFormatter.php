<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use IntlDateFormatter;
use IntlListFormatter;
use NumberFormatter;
use RuntimeException;

final readonly class LocaleFormatter
{
    public function __construct(
        private Translator $translator,
        private ConfigRepository $config,
        private Request $request,
    ) {}

    public function date(?DateTimeInterface $value, ?string $timezone = null): ?string
    {
        return $value === null
            ? null
            : $this->formatDate($value, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE, $timezone);
    }

    public function dateTime(?DateTimeInterface $value, ?string $timezone = null): ?string
    {
        return $value === null
            ? null
            : $this->formatDate($value, IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT, $timezone);
    }

    public function time(?DateTimeInterface $value, ?string $timezone = null): ?string
    {
        return $value === null
            ? null
            : $this->formatDate($value, IntlDateFormatter::NONE, IntlDateFormatter::SHORT, $timezone);
    }

    public function accessibleDateTime(
        ?DateTimeInterface $value,
        ?string $timezone = null,
    ): ?string {
        return $value === null
            ? null
            : $this->formatDate($value, IntlDateFormatter::FULL, IntlDateFormatter::SHORT, $timezone);
    }

    public function monthYear(?DateTimeInterface $value, ?string $timezone = null): ?string
    {
        return $value === null ? null : $this->formatPattern($value, 'LLLL y', $timezone);
    }

    public function monthDay(?DateTimeInterface $value, ?string $timezone = null): ?string
    {
        return $value === null ? null : $this->formatPattern($value, 'MMM d', $timezone);
    }

    public function weekdayMonthDay(
        ?DateTimeInterface $value,
        ?string $timezone = null,
    ): ?string {
        return $value === null ? null : $this->formatPattern($value, 'EEE, MMM d', $timezone);
    }

    public function weekdayShort(?DateTimeInterface $value, ?string $timezone = null): ?string
    {
        $formatted = $value === null ? null : $this->formatPattern($value, 'EEE', $timezone);

        return $formatted === null ? null : mb_strtoupper($formatted, 'UTF-8');
    }

    public function dayNumber(?DateTimeInterface $value, ?string $timezone = null): ?string
    {
        return $value === null ? null : $this->formatPattern($value, 'dd', $timezone);
    }

    public function relative(?DateTimeInterface $value, ?DateTimeInterface $other = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $localized = CarbonImmutable::instance($value)
            ->setTimezone($this->timezone())
            ->locale($this->locale());

        return $other === null
            ? $localized->diffForHumans()
            : $localized->diffForHumans(
                CarbonImmutable::instance($other)->setTimezone($this->timezone()),
                syntax: CarbonImmutable::DIFF_ABSOLUTE,
            );
    }

    public function number(
        int|float $value,
        int $maximumFractionDigits = 0,
        int $minimumFractionDigits = 0,
    ): string {
        $formatter = new NumberFormatter($this->locale(), NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $minimumFractionDigits);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maximumFractionDigits);

        return $this->formattedNumber($formatter, $value);
    }

    public function currency(int|float $value, string $currency): string
    {
        $formatter = new NumberFormatter($this->locale(), NumberFormatter::CURRENCY);
        $formatted = $formatter->formatCurrency($value, mb_strtoupper($currency, 'UTF-8'));

        if ($formatted === false) {
            throw new RuntimeException($formatter->getErrorMessage());
        }

        return $formatted;
    }

    public function percent(int|float $value, int $maximumFractionDigits = 0): string
    {
        $formatter = new NumberFormatter($this->locale(), NumberFormatter::PERCENT);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maximumFractionDigits);

        return $this->formattedNumber($formatter, $value / 100);
    }

    /**
     * @param  list<string>  $values
     */
    public function list(array $values): string
    {
        $formatter = new IntlListFormatter($this->locale());
        $formatted = $formatter->format($values);

        if ($formatted === false) {
            throw new RuntimeException($formatter->getErrorMessage());
        }

        return $formatted;
    }

    public function locale(): string
    {
        return $this->translator->getLocale();
    }

    public function timezone(): string
    {
        $user = $this->request->user();

        if ($user instanceof User) {
            return $user->timezone;
        }

        $timezone = $this->config->get('app.timezone', 'UTC');

        return is_string($timezone) ? $timezone : 'UTC';
    }

    private function formatDate(
        DateTimeInterface $value,
        int $dateStyle,
        int $timeStyle,
        ?string $timezone,
    ): string {
        $formatter = new IntlDateFormatter(
            $this->locale(),
            $dateStyle,
            $timeStyle,
            $timezone ?? $this->timezone(),
        );
        $formatted = $formatter->format($value);

        if ($formatted === false) {
            throw new RuntimeException($formatter->getErrorMessage());
        }

        return $formatted;
    }

    private function formatPattern(
        DateTimeInterface $value,
        string $pattern,
        ?string $timezone,
    ): string {
        $formatter = new IntlDateFormatter(
            $this->locale(),
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $timezone ?? $this->timezone(),
            pattern: $pattern,
        );
        $formatted = $formatter->format($value);

        if ($formatted === false) {
            throw new RuntimeException($formatter->getErrorMessage());
        }

        return $formatted;
    }

    private function formattedNumber(
        NumberFormatter $formatter,
        int|float $value,
    ): string {
        $formatted = $formatter->format($value);

        if ($formatted === false) {
            throw new RuntimeException($formatter->getErrorMessage());
        }

        return $formatted;
    }
}
