<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ForumTopicTypeSchema
{
    /**
     * @param  array<string, array{type: string, required: bool, validation: list<string>}>  $fields
     * @param  array<string, mixed>  $configuration
     */
    public function __construct(
        public ?int $databaseId,
        public string $stableKey,
        public string $nameTranslationKey,
        public string $descriptionTranslationKey,
        public int $schemaVersion,
        public array $fields,
        public array $configuration,
        public string $moderationLevel,
        public bool $allowsAcceptedAnswers,
        public bool $allowsConfirmation,
        public bool $expires,
    ) {}

    public function requiresLocation(): bool
    {
        return (bool) ($this->configuration['requires_location'] ?? false);
    }

    public function requiresSpecies(): bool
    {
        return (bool) ($this->configuration['requires_species'] ?? false);
    }

    public function allowsAttachment(string $attachment): bool
    {
        return in_array(
            $attachment,
            $this->stringList('allowed_attachments'),
            true,
        );
    }

    public function allowsReaction(string $reaction): bool
    {
        return in_array($reaction, $this->stringList('allowed_reactions'), true);
    }

    public function allowsNotificationLevel(string $level): bool
    {
        $levels = data_get($this->configuration, 'notifications.levels', []);

        return is_array($levels) && in_array($level, $levels, true);
    }

    /** @return list<string> */
    private function stringList(string $key): array
    {
        $values = $this->configuration[$key] ?? [];

        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(
            $values,
            static fn (mixed $value): bool => is_string($value),
        ));
    }
}
