<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ForumTopicType as ForumTopicTypeModel;
use App\Services\ForumTopicTypeSchemaCatalog;
use App\Services\ForumTopicTypeSchemaRegistry;
use Illuminate\Database\Seeder;

final class ForumTopicTypeSeeder extends Seeder
{
    public function __construct(
        private readonly ForumTopicTypeSchemaCatalog $catalog,
        private readonly ForumTopicTypeSchemaRegistry $registry,
    ) {}

    public function run(): void
    {
        $now = now();
        $rows = array_map(
            static fn (array $definition): array => [
                ...$definition,
                'field_schema' => json_encode(
                    $definition['field_schema'],
                    JSON_THROW_ON_ERROR,
                ),
                'configuration' => json_encode(
                    $definition['configuration'],
                    JSON_THROW_ON_ERROR,
                ),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $this->catalog->definitions(),
        );

        ForumTopicTypeModel::query()->upsert(
            $rows,
            ['stable_key'],
            [
                'name_translation_key',
                'description_translation_key',
                'schema_version',
                'field_schema',
                'configuration',
                'moderation_level',
                'allows_accepted_answers',
                'allows_confirmation',
                'expires',
                'is_system_managed',
                'is_active',
                'updated_at',
            ],
        );

        $this->registry->invalidate();
    }
}
