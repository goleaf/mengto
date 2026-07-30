<?php

namespace App\Services;

use Illuminate\Support\Str;

class SearchSafety
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

    /** @param array<string, mixed> $data */
    public function priority(array $data): string
    {
        return in_array($data['reason'] ?? null, [
            'animal-danger',
            'cruelty',
            'threat',
            'illegal-animal',
            'scam',
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
