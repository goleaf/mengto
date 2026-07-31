<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommunityAnimalGroup;
use App\Models\Taxon;
use Illuminate\Database\Seeder;

final class CommunityAnimalGroupSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = $this->definitions();
        $rows = [];
        $now = now();

        foreach ($definitions as $position => $definition) {
            $rows[] = [
                'stable_key' => $definition['key'],
                'name_translation_key' => "animal_taxonomy.groups.{$definition['key']}.name",
                'description_translation_key' => "animal_taxonomy.groups.{$definition['key']}.description",
                'position' => $position + 1,
                'is_system_managed' => true,
                'is_active' => true,
                'metadata' => json_encode(['core_seed' => true], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        CommunityAnimalGroup::query()->upsert(
            $rows,
            ['stable_key'],
            [
                'name_translation_key',
                'description_translation_key',
                'position',
                'is_system_managed',
                'is_active',
                'metadata',
                'updated_at',
            ],
        );

        $groups = CommunityAnimalGroup::query()
            ->whereIn('stable_key', array_column($definitions, 'key'))
            ->get()
            ->keyBy('stable_key');
        $taxa = Taxon::query()
            ->whereIn('stable_key', array_values(array_unique(array_merge(...array_column($definitions, 'taxa')))))
            ->get()
            ->keyBy('stable_key');

        foreach ($definitions as $definition) {
            $group = $groups->get($definition['key']);
            $sync = [];

            foreach ($definition['taxa'] as $position => $taxonKey) {
                $taxon = $taxa->get($taxonKey);

                if ($taxon !== null) {
                    $sync[$taxon->id] = [
                        'position' => $position + 1,
                        'includes_descendants' => true,
                    ];
                }
            }

            $group?->taxa()->sync($sync);
        }
    }

    /**
     * @return list<array{key: string, taxa: list<string>}>
     */
    private function definitions(): array
    {
        return [
            ['key' => 'companion-dogs', 'taxa' => ['taxon.core.canis-lupus-familiaris']],
            ['key' => 'companion-cats', 'taxa' => ['taxon.core.felis-catus']],
            ['key' => 'companion-small-mammals', 'taxa' => ['taxon.core.cavia-porcellus', 'taxon.core.mesocricetus-auratus']],
            ['key' => 'rabbits', 'taxa' => ['taxon.core.oryctolagus-cuniculus']],
            ['key' => 'rodents', 'taxa' => ['taxon.core.mus-musculus', 'taxon.core.rattus-norvegicus']],
            ['key' => 'ferrets', 'taxa' => ['taxon.core.mustela-furo']],
            ['key' => 'companion-birds', 'taxa' => ['taxon.core.melopsittacus-undulatus', 'taxon.core.nymphicus-hollandicus']],
            ['key' => 'poultry', 'taxa' => ['taxon.core.gallus-gallus-domesticus', 'taxon.core.anas-platyrhynchos-domesticus', 'taxon.core.anser-anser-domesticus']],
            ['key' => 'horses-equines', 'taxa' => ['taxon.core.equus-caballus', 'taxon.core.equus-asinus']],
            ['key' => 'farm-mammals', 'taxa' => ['taxon.core.bos-taurus', 'taxon.core.ovis-aries', 'taxon.core.capra-hircus', 'taxon.core.sus-scrofa-domesticus']],
            ['key' => 'aquarium-freshwater-fish', 'taxa' => ['taxon.core.carassius-auratus', 'taxon.core.betta-splendens']],
            ['key' => 'aquarium-marine-fish', 'taxa' => ['taxon.core.actinopterygii']],
            ['key' => 'pond-fish', 'taxa' => ['taxon.core.carassius-auratus']],
            ['key' => 'aquarium-crustaceans', 'taxa' => ['taxon.core.crustacea']],
            ['key' => 'aquarium-molluscs', 'taxa' => ['taxon.core.mollusca']],
            ['key' => 'corals-marine-invertebrates', 'taxa' => ['taxon.core.cnidaria', 'taxon.core.echinodermata']],
            ['key' => 'reptiles', 'taxa' => ['taxon.core.reptilia']],
            ['key' => 'amphibians', 'taxa' => ['taxon.core.amphibia']],
            ['key' => 'arachnids', 'taxa' => ['taxon.core.arachnida']],
            ['key' => 'insects', 'taxa' => ['taxon.core.insecta']],
            ['key' => 'myriapods', 'taxa' => ['taxon.core.myriapoda']],
            ['key' => 'terrestrial-molluscs', 'taxa' => ['taxon.core.mollusca']],
            ['key' => 'other-terrestrial-invertebrates', 'taxa' => ['taxon.core.annelida', 'taxon.core.onychophora']],
            ['key' => 'wildlife', 'taxa' => ['taxon.core.animalia']],
            ['key' => 'rescue-wildlife', 'taxa' => ['taxon.core.animalia']],
            ['key' => 'working-animals', 'taxa' => ['taxon.core.mammalia', 'taxon.core.aves']],
            ['key' => 'assistance-animals', 'taxa' => ['taxon.core.canis-lupus-familiaris']],
            ['key' => 'exotic-animals', 'taxa' => ['taxon.core.animalia']],
            ['key' => 'unidentified-animals', 'taxa' => ['taxon.core.animalia']],
            ['key' => 'other-animals', 'taxa' => ['taxon.core.animalia']],
        ];
    }
}
