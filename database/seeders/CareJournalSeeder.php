<?php

namespace Database\Seeders;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareRoutineStatus;
use App\Enums\CareSourceType;
use App\Enums\CareTaskPriority;
use App\Enums\CareTaskStatus;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\CareRoutine;
use App\Models\CareTask;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class CareJournalSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now('Europe/Vilnius');

        $scout = CareJournal::query()->updateOrCreate(
            ['owner_key' => 'mia-carter', 'pet_profile_key' => 'scout'],
            [
                'slug' => 'scout-care',
                'pet_name' => 'Scout',
                'species' => 'dog',
                'breed' => 'Border Collie mix',
                'image_url' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'privacy' => 'private',
                'timezone' => 'Europe/Vilnius',
                'current_caregiver_key' => 'mia-carter',
                'current_caregiver_name' => 'Mia Carter',
                'status' => 'active',
                'last_feeding_at' => $now->setTime(8, 5),
                'last_water_at' => $now->setTime(8, 12),
                'last_walk_at' => $now->setTime(7, 20),
                'last_toilet_at' => $now->setTime(7, 48),
            ],
        );

        $nori = CareJournal::query()->updateOrCreate(
            ['owner_key' => 'mia-carter', 'pet_profile_key' => 'nori'],
            [
                'slug' => 'nori-care',
                'pet_name' => 'Nori',
                'species' => 'cat',
                'breed' => 'Tabby',
                'image_url' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'privacy' => 'private',
                'timezone' => 'Europe/Vilnius',
                'current_caregiver_key' => 'mia-carter',
                'current_caregiver_name' => 'Mia Carter',
                'status' => 'active',
                'last_feeding_at' => $now->setTime(8, 30),
                'last_water_at' => $now->subDay()->setTime(20, 10),
                'last_toilet_at' => $now->setTime(6, 55),
            ],
        );

        $counter = 1;

        foreach (range(6, 1) as $daysAgo) {
            $date = $now->subDays($daysAgo);

            $counter = $this->entry($scout, $counter, [
                'type' => CareEntryType::Walk,
                'subtype' => 'morning-walk',
                'started_at' => $date->setTime(7, 20),
                'ended_at' => $date->setTime(7, 52),
                'title' => 'Morning neighborhood walk',
                'duration_minutes' => 32,
                'distance_meters' => 2300,
                'intensity' => 'moderate',
                'context' => [
                    'location_label' => 'Quiet neighborhood loop',
                    'weather' => 'Mild and dry',
                    'leash_behavior' => $daysAgo < 2 ? 'Calm near other dogs' : 'Needed distance near scooters',
                ],
            ]);
            $counter = $this->entry($scout, $counter, [
                'type' => CareEntryType::Feeding,
                'subtype' => 'breakfast',
                'started_at' => $date->setTime(8, 5),
                'title' => 'Breakfast',
                'quantity_value' => 150,
                'quantity_unit' => 'g',
                'appetite' => $daysAgo < 3 ? 'reduced' : 'normal',
                'measurements' => [
                    'planned_portion' => '150 g',
                    'offered_portion' => '150 g',
                    'eaten_portion' => $daysAgo < 3 ? 'about 90 g' : 'about 150 g',
                    'product' => 'Sensitive adult dry food',
                ],
                'notes' => $daysAgo < 3
                    ? 'Ate about half to two thirds of the planned portion.'
                    : 'Finished the planned portion.',
                'is_unusual' => $daysAgo < 3,
            ]);
            $counter = $this->entry($scout, $counter, [
                'type' => CareEntryType::Water,
                'subtype' => 'bowl-refresh',
                'started_at' => $date->setTime(8, 12),
                'title' => 'Kitchen water refreshed',
                'quantity_value' => 900,
                'quantity_unit' => 'ml',
                'measurements' => ['source' => 'Kitchen bowl', 'amount_filled' => '900 ml'],
            ]);
            $counter = $this->entry($scout, $counter, [
                'type' => CareEntryType::Toilet,
                'subtype' => 'stool',
                'started_at' => $date->setTime(7, 48),
                'title' => 'Morning toilet',
                'measurements' => ['consistency' => 'Formed', 'amount' => 'Usual'],
                'context' => ['location_label' => 'During the morning walk'],
            ]);
            $counter = $this->entry($scout, $counter, [
                'type' => CareEntryType::Training,
                'subtype' => 'calm-passing',
                'started_at' => $date->setTime(17, 35),
                'ended_at' => $date->setTime(17, 43),
                'title' => 'Calm passing practice',
                'duration_minutes' => 8,
                'intensity' => 'low',
                'notes' => 'Three short repetitions at a comfortable distance.',
                'context' => ['goal' => 'Pass dogs calmly', 'reward' => 'Food and distance'],
            ]);
            $counter = $this->entry($scout, $counter, [
                'type' => CareEntryType::Sleep,
                'subtype' => 'night-sleep',
                'started_at' => $date->subDay()->setTime(22, 30),
                'ended_at' => $date->setTime(6, 30),
                'title' => 'Night sleep',
                'duration_minutes' => $daysAgo === 1 ? 420 : 480,
                'measurements' => [
                    'quality' => $daysAgo === 1 ? 'Interrupted' : 'Calm',
                    'wake_ups' => $daysAgo === 1 ? 2 : 0,
                ],
                'is_unusual' => $daysAgo === 1,
            ]);
        }

        foreach ([
            [
                'type' => CareEntryType::Feeding,
                'subtype' => 'breakfast',
                'started_at' => $now->setTime(8, 5),
                'title' => 'Breakfast',
                'quantity_value' => 150,
                'quantity_unit' => 'g',
                'appetite' => 'reduced',
                'measurements' => [
                    'planned_portion' => '150 g',
                    'eaten_portion' => 'about 80 g',
                    'product' => 'Sensitive adult dry food',
                ],
                'notes' => 'Left almost half of breakfast. Water intake appears unchanged.',
                'is_unusual' => true,
            ],
            [
                'type' => CareEntryType::Water,
                'subtype' => 'bowl-refresh',
                'started_at' => $now->setTime(8, 12),
                'title' => 'Fresh water available',
                'quantity_value' => 900,
                'quantity_unit' => 'ml',
                'measurements' => ['source' => 'Kitchen bowl', 'amount_filled' => '900 ml'],
            ],
            [
                'type' => CareEntryType::Walk,
                'subtype' => 'morning-walk',
                'started_at' => $now->setTime(7, 20),
                'ended_at' => $now->setTime(7, 52),
                'title' => 'Morning neighborhood walk',
                'duration_minutes' => 32,
                'distance_meters' => 2400,
                'intensity' => 'moderate',
                'context' => ['location_label' => 'Quiet neighborhood loop', 'weather' => 'Clear'],
            ],
            [
                'type' => CareEntryType::Toilet,
                'subtype' => 'stool',
                'started_at' => $now->setTime(7, 48),
                'title' => 'Morning toilet',
                'measurements' => ['consistency' => 'Formed', 'amount' => 'Usual'],
            ],
            [
                'type' => CareEntryType::Activity,
                'subtype' => 'sniff-game',
                'started_at' => $now->setTime(12, 10),
                'ended_at' => $now->setTime(12, 25),
                'title' => 'Indoor scent game',
                'duration_minutes' => 15,
                'intensity' => 'low',
                'notes' => 'Engaged readily and settled after the game.',
            ],
        ] as $entry) {
            $counter = $this->entry($scout, $counter, $entry);
        }

        foreach ([
            [
                'type' => CareEntryType::Feeding,
                'subtype' => 'breakfast',
                'started_at' => $now->setTime(8, 30),
                'title' => 'Wet food breakfast',
                'quantity_value' => 85,
                'quantity_unit' => 'g',
                'appetite' => 'normal',
                'measurements' => ['product' => 'Indoor cat wet food', 'eaten_portion' => 'about 85 g'],
            ],
            [
                'type' => CareEntryType::Toilet,
                'subtype' => 'litter-box',
                'started_at' => $now->setTime(6, 55),
                'title' => 'Litter box visit',
                'measurements' => ['box' => 'Hallway box', 'observation' => 'Usual'],
            ],
            [
                'type' => CareEntryType::Activity,
                'subtype' => 'wand-toy',
                'started_at' => $now->subDay()->setTime(19, 20),
                'ended_at' => $now->subDay()->setTime(19, 32),
                'title' => 'Wand toy play',
                'duration_minutes' => 12,
                'intensity' => 'moderate',
            ],
            [
                'type' => CareEntryType::Grooming,
                'subtype' => 'brushing',
                'started_at' => $now->subDays(2)->setTime(18, 10),
                'title' => 'Coat brushing',
                'duration_minutes' => 6,
                'notes' => 'Tolerated brushing calmly. No matting noticed.',
            ],
        ] as $entry) {
            $counter = $this->entry($nori, $counter, $entry);
        }

        $this->routine($scout, 'Morning essentials', 'daily', '07:15', 'Walk, refresh water, breakfast, and record appetite.');
        $this->routine($scout, 'Evening wind-down', 'daily', '19:00', 'Dinner, medical dose from the health record, quiet play, and water check.');
        $this->routine($nori, 'Indoor cat evening', 'daily', '19:15', 'Interactive play, wet food, litter check, and fresh water.');

        $this->task($scout, 'Evening walk', CareEntryType::Walk, $now->setTime(18, 30), 'Mia Carter');
        $this->task($scout, 'Confirm evening medication in health record', CareEntryType::Observation, $now->setTime(19, 0), 'Mia Carter', true);
        $this->task($scout, 'Refresh bedroom water bowl', CareEntryType::Water, $now->setTime(20, 0), null);
        $this->task($nori, 'Clean hallway litter box', CareEntryType::Toilet, $now->setTime(18, 0), 'Mia Carter');
    }

    /** @param array<string, mixed> $data */
    private function entry(CareJournal $journal, int $counter, array $data): int
    {
        CareEntry::query()->updateOrCreate(
            ['idempotency_key' => sprintf('10000000-0000-4000-8000-%012d', $counter)],
            [
                'care_journal_id' => $journal->id,
                'type' => $data['type'],
                'subtype' => $data['subtype'] ?? null,
                'started_at' => $data['started_at'],
                'ended_at' => $data['ended_at'] ?? null,
                'timezone' => $journal->timezone,
                'status' => CareEntryStatus::Completed,
                'source_type' => CareSourceType::Owner,
                'source_name' => 'Mia Carter',
                'verification_status' => 'person-reported',
                'author_key' => 'mia-carter',
                'author_name' => 'Mia Carter',
                'title' => $data['title'],
                'notes' => $data['notes'] ?? null,
                'measurements' => $data['measurements'] ?? [],
                'context' => $data['context'] ?? [],
                'quantity_value' => $data['quantity_value'] ?? null,
                'quantity_unit' => $data['quantity_unit'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'distance_meters' => $data['distance_meters'] ?? null,
                'appetite' => $data['appetite'] ?? null,
                'intensity' => $data['intensity'] ?? null,
                'is_unusual' => $data['is_unusual'] ?? false,
                'privacy' => 'private',
            ],
        );

        return $counter + 1;
    }

    private function routine(
        CareJournal $journal,
        string $name,
        string $period,
        string $startTime,
        string $instructions,
    ): void {
        CareRoutine::query()->updateOrCreate(
            ['care_journal_id' => $journal->id, 'name' => $name],
            [
                'period' => $period,
                'starts_on' => today(),
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'start_time' => $startTime,
                'timezone' => $journal->timezone,
                'status' => CareRoutineStatus::Active,
                'version' => 1,
                'instructions' => $instructions,
                'created_by_key' => 'mia-carter',
                'created_by_name' => 'Mia Carter',
            ],
        );
    }

    private function task(
        CareJournal $journal,
        string $title,
        CareEntryType $type,
        CarbonImmutable $dueAt,
        ?string $assignee,
        bool $requiresConfirmation = false,
    ): void {
        CareTask::query()->updateOrCreate(
            ['care_journal_id' => $journal->id, 'title' => $title],
            [
                'type' => $type,
                'assignee_key' => $assignee ? 'mia-carter' : null,
                'assignee_name' => $assignee,
                'due_at' => $dueAt,
                'timezone' => $journal->timezone,
                'priority' => $requiresConfirmation
                    ? CareTaskPriority::Important
                    : CareTaskPriority::Normal,
                'status' => CareTaskStatus::Planned,
                'instructions' => $requiresConfirmation
                    ? 'Use the medical record as the source of truth. Do not create a duplicate dose entry.'
                    : null,
                'requires_individual_confirmation' => $requiresConfirmation,
                'completed_at' => null,
                'completed_by_key' => null,
                'completed_by_name' => null,
                'completion_note' => null,
                'created_by_key' => 'mia-carter',
                'created_by_name' => 'Mia Carter',
            ],
        );
    }
}
