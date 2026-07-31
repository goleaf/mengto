<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TaxonSource;
use Illuminate\Database\Seeder;

final class CatalogueOfLifeSourceSeeder extends Seeder
{
    public function run(): void
    {
        TaxonSource::query()->updateOrCreate(
            ['stable_key' => 'catalogue-of-life-base'],
            [
                'name' => 'Catalogue of Life Base Release',
                'source_type' => 'darwin-core-archive',
                'version' => '2026-07-14',
                'release_date' => '2026-07-14',
                'downloaded_at' => null,
                'checksum' => null,
                'license' => 'Creative Commons Attribution 4.0 International',
                'attribution' => 'Catalogue of Life (2026-07-14 Base Release), Catalogue of Life Foundation.',
                'source_url' => 'https://www.catalogueoflife.org/data/download',
                'import_priority' => 20,
                'is_active' => true,
                'metadata' => [
                    'role' => 'primary-broad-taxonomic-snapshot',
                    'format' => 'Darwin Core Archive',
                    'requires_local_snapshot' => true,
                    'normal_requests_use_external_api' => false,
                    'license_url' => 'https://creativecommons.org/licenses/by/4.0/',
                ],
            ],
        );
    }
}
