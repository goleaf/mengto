<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TaxonImportState;
use App\Models\Taxon;
use App\Models\TaxonImport;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CoreAnimalTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $source = TaxonSource::query()->updateOrCreate(
                ['stable_key' => 'platform-core-animal-taxonomy'],
                [
                    'name' => 'PawCircle core animal taxonomy',
                    'source_type' => 'curated-core-seed',
                    'version' => '1',
                    'release_date' => '2026-07-31',
                    'downloaded_at' => null,
                    'checksum' => $this->checksum(),
                    'license' => 'PawCircle first-party data',
                    'attribution' => 'PawCircle core taxonomy; scientific names are enriched by versioned external imports.',
                    'source_url' => 'https://pawcircle.example.test/taxonomy/core',
                    'import_priority' => 10,
                    'is_active' => true,
                    'metadata' => [
                        'purpose' => 'Production-safe bootstrap taxonomy',
                        'is_complete_species_catalogue' => false,
                    ],
                ],
            );
            $import = TaxonImport::query()->updateOrCreate(
                [
                    'taxon_source_id' => $source->id,
                    'source_version' => '1',
                    'checksum' => $this->checksum(),
                ],
                [
                    'state' => TaxonImportState::Active,
                    'started_at' => now(),
                    'completed_at' => now(),
                    'activated_at' => now(),
                    'impact_report' => ['kind' => 'core-seed'],
                    'error_report' => [],
                    'metadata' => ['idempotent' => true],
                ],
            );
            $taxaByKey = [];
            $pathsByKey = [];

            foreach ($this->definitions() as $position => $definition) {
                $parent = $definition['parent'] === null
                    ? null
                    : ($taxaByKey[$definition['parent']] ?? null);

                if ($definition['parent'] !== null && ! $parent instanceof Taxon) {
                    throw new \LogicException("Core taxon parent {$definition['parent']} must be defined first.");
                }

                $taxon = Taxon::query()->updateOrCreate(
                    ['stable_key' => $definition['key']],
                    [
                        'resolution_status' => 'accepted',
                        'requires_review' => false,
                        'is_active' => true,
                        'metadata' => [
                            'core_seed_position' => $position,
                            'source' => 'platform-core-animal-taxonomy',
                        ],
                        'archived_at' => null,
                    ],
                );
                $taxaByKey[$definition['key']] = $taxon;
                $pathsByKey[$definition['key']] = $parent === null
                    ? $definition['key']
                    : $pathsByKey[$definition['parent']].'/'.$definition['key'];
                $canonical = $definition['scientific_name'];
                $normalized = Str::lower($canonical);

                TaxonVersion::query()->updateOrCreate(
                    [
                        'taxon_id' => $taxon->id,
                        'taxon_import_id' => $import->id,
                    ],
                    [
                        'taxon_source_id' => $source->id,
                        'parent_taxon_id' => $parent?->id,
                        'source_record_id' => $definition['key'],
                        'rank' => $definition['rank'],
                        'scientific_name' => $canonical,
                        'canonical_name' => $canonical,
                        'normalized_scientific_name' => $normalized,
                        'authorship' => null,
                        'nomenclatural_code' => 'ICZN',
                        'taxonomic_status' => 'accepted',
                        'depth' => $definition['depth'],
                        'hierarchy_path' => $pathsByKey[$definition['key']],
                        'is_extinct' => false,
                        'is_fossil' => false,
                        'is_marine' => $definition['marine'],
                        'is_freshwater' => $definition['freshwater'],
                        'is_terrestrial' => $definition['terrestrial'],
                        'has_domestic_relevance' => $definition['domestic'],
                        'has_community_relevance' => true,
                        'is_active_version' => true,
                        'metadata' => ['core_seed' => true],
                    ],
                );

                TaxonName::query()->updateOrCreate(
                    [
                        'taxon_id' => $taxon->id,
                        'taxon_import_id' => $import->id,
                        'name_type' => 'scientific',
                        'normalized_name' => $normalized,
                    ],
                    [
                        'taxon_source_id' => $source->id,
                        'locale' => null,
                        'language' => null,
                        'script' => 'Latn',
                        'name' => $canonical,
                        'source_record_id' => $definition['key'],
                        'is_preferred' => true,
                        'is_verified' => true,
                        'is_local_override' => false,
                        'is_active' => true,
                        'metadata' => [],
                    ],
                );

                TaxonName::query()->updateOrCreate(
                    [
                        'taxon_id' => $taxon->id,
                        'taxon_import_id' => $import->id,
                        'name_type' => 'preferred common',
                        'locale' => 'en',
                    ],
                    [
                        'taxon_source_id' => $source->id,
                        'language' => 'English',
                        'script' => 'Latn',
                        'name' => $definition['common_name'],
                        'normalized_name' => Str::lower($definition['common_name']),
                        'source_record_id' => $definition['key'].':en',
                        'is_preferred' => true,
                        'is_verified' => true,
                        'is_local_override' => true,
                        'is_active' => true,
                        'metadata' => ['core_seed' => true],
                    ],
                );
            }

            $import->update([
                'processed_rows' => count($this->definitions()),
                'inserted_rows' => count($this->definitions()),
            ]);
            $source->update(['active_taxon_import_id' => $import->id]);
        }, 3);
    }

    private function checksum(): string
    {
        return hash(
            'sha256',
            json_encode($this->definitions(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
        );
    }

    /**
     * @return list<array{
     *     key: string,
     *     parent: string|null,
     *     rank: string,
     *     scientific_name: string,
     *     common_name: string,
     *     depth: int,
     *     marine: bool|null,
     *     freshwater: bool|null,
     *     terrestrial: bool|null,
     *     domestic: bool
     * }>
     */
    private function definitions(): array
    {
        $rows = [
            ['animalia', null, 'kingdom', 'Animalia', 'Animals'],
            ['chordata', 'animalia', 'phylum', 'Chordata', 'Chordates'],
            ['mammalia', 'chordata', 'class', 'Mammalia', 'Mammals'],
            ['aves', 'chordata', 'class', 'Aves', 'Birds'],
            ['reptilia', 'chordata', 'class', 'Reptilia', 'Reptiles'],
            ['amphibia', 'chordata', 'class', 'Amphibia', 'Amphibians'],
            ['actinopterygii', 'chordata', 'class', 'Actinopterygii', 'Ray-finned fishes'],
            ['sarcopterygii', 'chordata', 'class', 'Sarcopterygii', 'Lobe-finned fishes'],
            ['chondrichthyes', 'chordata', 'class', 'Chondrichthyes', 'Cartilaginous fishes'],
            ['myxini', 'chordata', 'class', 'Myxini', 'Hagfishes'],
            ['petromyzonti', 'chordata', 'class', 'Petromyzonti', 'Lampreys'],
            ['tunicata', 'chordata', 'subphylum', 'Tunicata', 'Tunicates'],
            ['cephalochordata', 'chordata', 'subphylum', 'Cephalochordata', 'Lancelets'],
            ['arthropoda', 'animalia', 'phylum', 'Arthropoda', 'Arthropods'],
            ['insecta', 'arthropoda', 'class', 'Insecta', 'Insects'],
            ['arachnida', 'arthropoda', 'class', 'Arachnida', 'Arachnids'],
            ['crustacea', 'arthropoda', 'subphylum', 'Crustacea', 'Crustaceans'],
            ['myriapoda', 'arthropoda', 'subphylum', 'Myriapoda', 'Myriapods'],
            ['xiphosura', 'arthropoda', 'order', 'Xiphosura', 'Horseshoe crabs'],
            ['pycnogonida', 'arthropoda', 'class', 'Pycnogonida', 'Sea spiders'],
            ['mollusca', 'animalia', 'phylum', 'Mollusca', 'Molluscs'],
            ['annelida', 'animalia', 'phylum', 'Annelida', 'Segmented worms'],
            ['cnidaria', 'animalia', 'phylum', 'Cnidaria', 'Cnidarians'],
            ['echinodermata', 'animalia', 'phylum', 'Echinodermata', 'Echinoderms'],
            ['porifera', 'animalia', 'phylum', 'Porifera', 'Sponges'],
            ['ctenophora', 'animalia', 'phylum', 'Ctenophora', 'Comb jellies'],
            ['platyhelminthes', 'animalia', 'phylum', 'Platyhelminthes', 'Flatworms'],
            ['nematoda', 'animalia', 'phylum', 'Nematoda', 'Roundworms'],
            ['nemertea', 'animalia', 'phylum', 'Nemertea', 'Ribbon worms'],
            ['rotifera', 'animalia', 'phylum', 'Rotifera', 'Rotifers'],
            ['acanthocephala', 'animalia', 'phylum', 'Acanthocephala', 'Thorny-headed worms'],
            ['tardigrada', 'animalia', 'phylum', 'Tardigrada', 'Tardigrades'],
            ['onychophora', 'animalia', 'phylum', 'Onychophora', 'Velvet worms'],
            ['bryozoa', 'animalia', 'phylum', 'Bryozoa', 'Bryozoans'],
            ['brachiopoda', 'animalia', 'phylum', 'Brachiopoda', 'Brachiopods'],
            ['phoronida', 'animalia', 'phylum', 'Phoronida', 'Phoronids'],
            ['hemichordata', 'animalia', 'phylum', 'Hemichordata', 'Hemichordates'],
            ['xenacoelomorpha', 'animalia', 'phylum', 'Xenacoelomorpha', 'Xenacoelomorphs'],
            ['placozoa', 'animalia', 'phylum', 'Placozoa', 'Placozoans'],
            ['gastrotricha', 'animalia', 'phylum', 'Gastrotricha', 'Gastrotrichs'],
            ['kinorhyncha', 'animalia', 'phylum', 'Kinorhyncha', 'Kinorhynchs'],
            ['priapulida', 'animalia', 'phylum', 'Priapulida', 'Priapulids'],
            ['loricifera', 'animalia', 'phylum', 'Loricifera', 'Loriciferans'],
            ['nematomorpha', 'animalia', 'phylum', 'Nematomorpha', 'Horsehair worms'],
            ['chaetognatha', 'animalia', 'phylum', 'Chaetognatha', 'Arrow worms'],
            ['entoprocta', 'animalia', 'phylum', 'Entoprocta', 'Entoprocts'],
            ['cycliophora', 'animalia', 'phylum', 'Cycliophora', 'Cycliophorans'],
            ['gnathostomulida', 'animalia', 'phylum', 'Gnathostomulida', 'Gnathostomulids'],
            ['micrognathozoa', 'animalia', 'phylum', 'Micrognathozoa', 'Micrognathozoans'],
            ['dicyemida', 'animalia', 'phylum', 'Dicyemida', 'Dicyemids'],
            ['orthonectida', 'animalia', 'phylum', 'Orthonectida', 'Orthonectids'],
            ['canis-lupus-familiaris', 'mammalia', 'subspecies', 'Canis lupus familiaris', 'Domestic dog'],
            ['felis-catus', 'mammalia', 'species', 'Felis catus', 'Domestic cat'],
            ['oryctolagus-cuniculus', 'mammalia', 'species', 'Oryctolagus cuniculus', 'European rabbit'],
            ['equus-caballus', 'mammalia', 'species', 'Equus caballus', 'Domestic horse'],
            ['equus-asinus', 'mammalia', 'species', 'Equus asinus', 'Domestic donkey'],
            ['bos-taurus', 'mammalia', 'species', 'Bos taurus', 'Cattle'],
            ['ovis-aries', 'mammalia', 'species', 'Ovis aries', 'Sheep'],
            ['capra-hircus', 'mammalia', 'species', 'Capra hircus', 'Domestic goat'],
            ['sus-scrofa-domesticus', 'mammalia', 'subspecies', 'Sus scrofa domesticus', 'Domestic pig'],
            ['mustela-furo', 'mammalia', 'species', 'Mustela furo', 'Domestic ferret'],
            ['cavia-porcellus', 'mammalia', 'species', 'Cavia porcellus', 'Guinea pig'],
            ['mesocricetus-auratus', 'mammalia', 'species', 'Mesocricetus auratus', 'Golden hamster'],
            ['mus-musculus', 'mammalia', 'species', 'Mus musculus', 'House mouse'],
            ['rattus-norvegicus', 'mammalia', 'species', 'Rattus norvegicus', 'Brown rat'],
            ['gallus-gallus-domesticus', 'aves', 'subspecies', 'Gallus gallus domesticus', 'Domestic chicken'],
            ['anas-platyrhynchos-domesticus', 'aves', 'subspecies', 'Anas platyrhynchos domesticus', 'Domestic duck'],
            ['anser-anser-domesticus', 'aves', 'subspecies', 'Anser anser domesticus', 'Domestic goose'],
            ['meleagris-gallopavo', 'aves', 'species', 'Meleagris gallopavo', 'Turkey'],
            ['columba-livia-domestica', 'aves', 'subspecies', 'Columba livia domestica', 'Domestic pigeon'],
            ['melopsittacus-undulatus', 'aves', 'species', 'Melopsittacus undulatus', 'Budgerigar'],
            ['nymphicus-hollandicus', 'aves', 'species', 'Nymphicus hollandicus', 'Cockatiel'],
            ['carassius-auratus', 'actinopterygii', 'species', 'Carassius auratus', 'Goldfish'],
            ['betta-splendens', 'actinopterygii', 'species', 'Betta splendens', 'Siamese fighting fish'],
        ];

        $depthByKey = [];

        return array_map(
            static function (array $row) use (&$depthByKey): array {
                [$suffix, $parent, $rank, $scientificName, $commonName] = $row;
                $key = 'taxon.core.'.$suffix;
                $parentKey = $parent === null ? null : 'taxon.core.'.$parent;
                $depth = $parentKey === null ? 0 : ($depthByKey[$parentKey] + 1);
                $depthByKey[$key] = $depth;
                $isAquatic = in_array($parentKey, [
                    'taxon.core.actinopterygii',
                    'taxon.core.sarcopterygii',
                    'taxon.core.chondrichthyes',
                    'taxon.core.myxini',
                    'taxon.core.petromyzonti',
                ], true);
                $domestic = $depth >= 3;

                return [
                    'key' => $key,
                    'parent' => $parentKey,
                    'rank' => $rank,
                    'scientific_name' => $scientificName,
                    'common_name' => $commonName,
                    'depth' => $depth,
                    'marine' => $isAquatic ? null : false,
                    'freshwater' => $isAquatic ? null : false,
                    'terrestrial' => $isAquatic ? null : true,
                    'domestic' => $domestic,
                ];
            },
            $rows,
        );
    }
}
