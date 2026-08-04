<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetIdentifyingMarkType;
use App\Enums\PetIdentifyingMarkVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileIdentifyingMark;
use Illuminate\Support\Str;
use LogicException;

final class PetIdentifyingMarkSynchronizer
{
    /**
     * @param list<array{
     *     mark_key: string|null,
     *     type: PetIdentifyingMarkType,
     *     description: string,
     *     visibility: PetIdentifyingMarkVisibility
     * }> $marks
     */
    public function differs(PetProfile $profile, array $marks): bool
    {
        $this->ensureLoaded($profile);

        $current = $profile->activeIdentifyingMarks
            ->values()
            ->map(static fn (PetProfileIdentifyingMark $mark): array => [
                'mark_key' => $mark->mark_key,
                'type' => $mark->type->value,
                'description' => $mark->description,
                'visibility' => $mark->visibility->value,
            ])
            ->all();
        $expected = array_map(static fn (array $mark): array => [
            'mark_key' => $mark['mark_key'],
            'type' => $mark['type']->value,
            'description' => $mark['description'],
            'visibility' => $mark['visibility']->value,
        ], $marks);

        return $current !== $expected;
    }

    /**
     * @param list<array{
     *     mark_key: string|null,
     *     type: PetIdentifyingMarkType,
     *     description: string,
     *     visibility: PetIdentifyingMarkVisibility
     * }> $marks
     */
    public function sync(PetProfile $profile, array $marks, int $actorId): void
    {
        $this->ensureLoaded($profile);

        $currentKeys = $profile->activeIdentifyingMarks
            ->pluck('mark_key')
            ->filter(static fn (mixed $key): bool => is_string($key))
            ->flip();
        $usedKeys = [];
        $now = now();
        $rows = [];

        foreach ($marks as $position => $mark) {
            $requestedKey = $mark['mark_key'];
            $markKey = is_string($requestedKey)
                && $currentKeys->has($requestedKey)
                && ! isset($usedKeys[$requestedKey])
                    ? $requestedKey
                    : Str::lower((string) Str::ulid());
            $usedKeys[$markKey] = true;
            $encrypted = new PetProfileIdentifyingMark;
            $encrypted->description = $mark['description'];
            $isExisting = $currentKeys->has($markKey);
            $rows[] = [
                'mark_key' => $markKey,
                'pet_profile_id' => $profile->id,
                'type' => $mark['type']->value,
                'description' => $encrypted->getAttributes()['description'],
                'visibility' => $mark['visibility']->value,
                'position' => $position,
                'created_by_user_id' => $isExisting
                    ? $profile->activeIdentifyingMarks->firstWhere('mark_key', $markKey)?->created_by_user_id
                    : $actorId,
                'updated_by_user_id' => $actorId,
                'retired_at' => null,
                'created_at' => $isExisting
                    ? $profile->activeIdentifyingMarks->firstWhere('mark_key', $markKey)?->created_at
                    : $now,
                'updated_at' => $now,
            ];
        }

        $stale = PetProfileIdentifyingMark::query()
            ->where('pet_profile_id', $profile->id)
            ->active();

        if ($usedKeys !== []) {
            $stale->whereNotIn('mark_key', array_keys($usedKeys));
        }

        $stale->update([
            'updated_by_user_id' => $actorId,
            'retired_at' => $now,
            'updated_at' => $now,
        ]);

        if ($rows !== []) {
            PetProfileIdentifyingMark::query()->upsert(
                $rows,
                ['mark_key'],
                [
                    'type',
                    'description',
                    'visibility',
                    'position',
                    'updated_by_user_id',
                    'retired_at',
                    'updated_at',
                ],
            );
        }

        $profile->unsetRelation('activeIdentifyingMarks');
        $profile->unsetRelation('identifyingMarks');
    }

    private function ensureLoaded(PetProfile $profile): void
    {
        if (! $profile->relationLoaded('activeIdentifyingMarks')) {
            throw new LogicException(__('pet_profiles.validation.identifying_marks_not_loaded'));
        }
    }
}
