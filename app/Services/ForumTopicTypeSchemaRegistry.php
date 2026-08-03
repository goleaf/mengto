<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ForumTopicTypeSchema;
use App\Models\ForumTopicType;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final readonly class ForumTopicTypeSchemaRegistry
{
    public const CACHE_KEY = 'forum:topic-type-schemas:v1';

    private const MAX_DEFINITIONS = 200;

    public function __construct(
        private ForumTopicTypeSchemaCatalog $catalog,
        private CacheRepository $cache,
    ) {}

    public function definition(string $stableKey): ?ForumTopicTypeSchema
    {
        return $this->definitions()[$stableKey] ?? null;
    }

    /** @return array<string, ForumTopicTypeSchema> */
    public function definitions(): array
    {
        $rows = $this->cache->remember(
            self::CACHE_KEY,
            now()->addSeconds(max(
                1,
                (int) config('taxonomy.topic_type_schema_cache_seconds'),
            )),
            fn (): array => $this->loadRows(),
        );

        $definitions = [];

        foreach ($rows as $row) {
            if (! is_array($row) || ! ($row['is_active'] ?? false)) {
                continue;
            }

            $definition = $this->hydrate($row);
            $definitions[$definition->stableKey] = $definition;
        }

        return $definitions;
    }

    public function invalidate(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    /** @return list<array<string, mixed>> */
    private function loadRows(): array
    {
        $rows = ForumTopicType::query()
            ->select([
                'id',
                'stable_key',
                'name_translation_key',
                'description_translation_key',
                'schema_version',
                'field_schema',
                'configuration',
                'moderation_level',
                'allows_accepted_answers',
                'allows_confirmation',
                'expires',
                'is_active',
            ])
            ->orderBy('id')
            ->limit(self::MAX_DEFINITIONS)
            ->get();

        if ($rows->isEmpty()) {
            return $this->catalogRows();
        }

        return $rows
            ->map(static fn (ForumTopicType $definition): array => [
                'id' => $definition->id,
                'stable_key' => $definition->stable_key,
                'name_translation_key' => $definition->name_translation_key,
                'description_translation_key' => $definition->description_translation_key,
                'schema_version' => $definition->schema_version,
                'field_schema' => $definition->field_schema ?? [],
                'configuration' => $definition->configuration ?? [],
                'moderation_level' => $definition->moderation_level,
                'allows_accepted_answers' => $definition->allows_accepted_answers,
                'allows_confirmation' => $definition->allows_confirmation,
                'expires' => $definition->expires,
                'is_active' => $definition->is_active,
            ])
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function catalogRows(): array
    {
        return array_map(
            static fn (array $definition): array => [
                'id' => null,
                ...$definition,
            ],
            $this->catalog->definitions(),
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): ForumTopicTypeSchema
    {
        $fields = $row['field_schema'] ?? [];
        $configuration = $row['configuration'] ?? [];

        return new ForumTopicTypeSchema(
            databaseId: is_int($row['id'] ?? null) ? $row['id'] : null,
            stableKey: (string) $row['stable_key'],
            nameTranslationKey: (string) $row['name_translation_key'],
            descriptionTranslationKey: (string) $row['description_translation_key'],
            schemaVersion: max(1, (int) $row['schema_version']),
            fields: is_array($fields) ? $fields : [],
            configuration: is_array($configuration) ? $configuration : [],
            moderationLevel: (string) $row['moderation_level'],
            allowsAcceptedAnswers: (bool) $row['allows_accepted_answers'],
            allowsConfirmation: (bool) $row['allows_confirmation'],
            expires: (bool) $row['expires'],
        );
    }
}
