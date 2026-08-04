<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetIdentifyingMarkType;
use App\Enums\PetIdentifyingMarkVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileIdentifyingMark;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

final class PetIdentifyingMarkNormalizer
{
    public const MAX_DESCRIPTION_LENGTH = 500;

    public const MAX_MARKS = 12;

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{
     *     mark_key: string|null,
     *     type: PetIdentifyingMarkType,
     *     description: string,
     *     visibility: PetIdentifyingMarkVisibility
     * }>|null
     */
    public function normalize(array $data, PetProfile $profile): ?array
    {
        if (! array_key_exists('identifying_marks_items', $data)) {
            return null;
        }

        if (! $profile->relationLoaded('activeIdentifyingMarks')) {
            throw new LogicException(__('pet_profiles.validation.identifying_marks_not_loaded'));
        }

        $items = $data['identifying_marks_items'];

        if (! is_array($items) || ! array_is_list($items) || count($items) > self::MAX_MARKS) {
            $this->invalid('identifying_marks_items', 'identifying_mark_items');
        }

        $existing = $profile->activeIdentifyingMarks->keyBy('id');
        $usedIds = [];
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                $this->invalid("identifying_marks_items.{$index}", 'identifying_mark_items');
            }

            $id = $this->nullableId($item['id'] ?? null, $index);
            $existingMark = $id === null ? null : $existing->get($id);

            if ($id !== null
                && (! $existingMark instanceof PetProfileIdentifyingMark || isset($usedIds[$id]))) {
                $this->invalid("identifying_marks_items.{$index}.id", 'identifying_mark_id');
            }

            if ($id !== null) {
                $usedIds[$id] = true;
            }

            $normalized[] = [
                'mark_key' => $existingMark?->mark_key,
                'type' => $this->type($item['type'] ?? null, $index),
                'description' => $this->description($item['description'] ?? null, $index),
                'visibility' => $this->visibility($item['visibility'] ?? null, $index),
            ];
        }

        return $normalized;
    }

    private function nullableId(mixed $id, int $index): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (! is_int($id) || $id < 1) {
            $this->invalid("identifying_marks_items.{$index}.id", 'identifying_mark_id');
        }

        return $id;
    }

    private function type(mixed $value, int $index): PetIdentifyingMarkType
    {
        $type = is_string($value) ? PetIdentifyingMarkType::tryFrom($value) : null;

        if (! $type instanceof PetIdentifyingMarkType) {
            $this->invalid("identifying_marks_items.{$index}.type", 'identifying_mark_type');
        }

        return $type;
    }

    private function description(mixed $value, int $index): string
    {
        if (! is_string($value)) {
            $this->invalid("identifying_marks_items.{$index}.description", 'identifying_mark_description');
        }

        $description = trim($value);

        if ($description === '' || Str::length($description) > self::MAX_DESCRIPTION_LENGTH) {
            $this->invalid("identifying_marks_items.{$index}.description", 'identifying_mark_description');
        }

        return $description;
    }

    private function visibility(mixed $value, int $index): PetIdentifyingMarkVisibility
    {
        $visibility = is_string($value)
            ? PetIdentifyingMarkVisibility::tryFrom($value)
            : null;

        if (! $visibility instanceof PetIdentifyingMarkVisibility) {
            $this->invalid("identifying_marks_items.{$index}.visibility", 'identifying_mark_visibility');
        }

        return $visibility;
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => __("pet_profiles.validation.{$message}"),
        ]);
    }
}
