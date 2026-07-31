<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class SearchSafety
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{flags: array<int, string>, manual_review: bool}
     */
    public function assessCase(array $data): array
    {
        $text = $this->text($data, [
            'description',
            'distinctive_marks',
            'health_notice',
            'approach_instructions',
            'avoid_instructions',
        ]);
        $flags = [];

        if ($this->contains($text, ['bank account', 'card number', 'verification code', 'one-time code'])) {
            $flags[] = 'sensitive-payment-data';
        }

        if ($this->contains($text, ['pay first', 'transfer money', 'crypto wallet', 'gift card'])) {
            $flags[] = 'suspicious-payment-request';
        }

        if ($this->contains($text, ['kill', 'hurt the animal', 'ransom'])) {
            $flags[] = 'threat-language';
        }

        if ($this->contains($text, ['for sale', 'buy now', 'highest bidder'])) {
            $flags[] = 'possible-hidden-sale';
        }

        return [
            'flags' => array_values(array_unique($flags)),
            'manual_review' => $flags !== [],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{flags: array<int, string>, manual_review: bool}
     */
    public function assessSighting(array $data): array
    {
        $text = $this->text($data, ['notes', 'danger', 'animal_condition']);
        $flags = [];

        if ($this->contains($text, ['pay me', 'send money', 'verification code', 'password'])) {
            $flags[] = 'possible-scam';
        }

        if ($this->contains($text, ['http://', 'https://', 'bit.ly', 'tinyurl'])) {
            $flags[] = 'external-link';
        }

        return [
            'flags' => $flags,
            'manual_review' => $flags !== [],
        ];
    }

    public function rewardSummaryIsSafe(?string $summary): bool
    {
        if (blank($summary)) {
            return true;
        }

        $normalized = Str::lower((string) $summary);

        if ($this->contains($normalized, [
            'bank account',
            'card number',
            'verification code',
            'one-time code',
            'transfer money',
            'crypto wallet',
            'gift card',
            'paypal',
            'venmo',
            'cashapp',
            'http://',
            'https://',
        ])) {
            return false;
        }

        return preg_match('/\\b[A-Z]{2}\\d{2}[A-Z0-9]{10,30}\\b/i', $summary) !== 1
            && preg_match('/\\b\\+?\\d[\\d\\s().-]{7,}\\d\\b/', $summary) !== 1
            && filter_var($summary, FILTER_VALIDATE_EMAIL) === false;
    }

    /** @param array<string, mixed> $data */
    public function priority(array $data): string
    {
        return in_array($data['reason'] ?? null, [
            'animal-danger',
            'cruelty',
            'threat',
            'illegal-animal',
            'scam',
            'reward-scam',
            'lost-animal-scam',
            'false-lost-animal-sighting',
            'threats',
            'animal-cruelty',
        ], true) ? 'high' : 'normal';
    }

    /** @param array<string, mixed> $data @param array<int, string> $fields */
    private function text(array $data, array $fields): string
    {
        return Str::lower(collect($fields)
            ->map(fn (string $field): string => (string) ($data[$field] ?? ''))
            ->join(' '));
    }

    /** @param array<int, string> $needles */
    private function contains(string $text, array $needles): bool
    {
        return Str::contains($text, $needles, true);
    }
}
