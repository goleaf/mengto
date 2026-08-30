<?php

declare(strict_types=1);

namespace App\Services;

use JsonException;
use RuntimeException;

final class ForumCategoryCatalog
{
    private const ROOT_CATEGORY_COUNT = 44;

    private const SCHEMA_VERSION = 1;

    private const SOURCE_PAYLOAD_SHA256 = '6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773';

    private const SUBCATEGORY_COUNT = 1637;

    public function __construct(
        private readonly ?string $manifestPath = null,
    ) {}

    /**
     * @return array{
     *     schema_version: int,
     *     source_payload_sha256: string,
     *     root_category_count: int,
     *     subcategory_count: int,
     *     categories: list<array{
     *         number: int,
     *         stable_key: string,
     *         slug: string,
     *         name: string,
     *         purpose: string,
     *         icon: string,
     *         source: string,
     *         subcategories: list<array{stable_key: string, slug: string, name: string}>
     *     }>
     * }
     */
    public function load(): array
    {
        $path = $this->manifestPath ?? resource_path('data/forum/categories.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read forum category manifest at {$path}.");
        }

        try {
            $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(__('messages.the_forum_category_manifest_is_invalid_json'), previous: $exception);
        }

        if (! is_array($manifest)) {
            throw new RuntimeException(__('messages.the_forum_category_manifest_must_contain_all_44_root_categories'));
        }

        $this->assertValidManifest($manifest);

        /** @var array{
         *     schema_version: int,
         *     source_payload_sha256: string,
         *     root_category_count: int,
         *     subcategory_count: int,
         *     categories: list<array{
         *         number: int,
         *         stable_key: string,
         *         slug: string,
         *         name: string,
         *         purpose: string,
         *         icon: string,
         *         source: string,
         *         subcategories: list<array{stable_key: string, slug: string, name: string}>
         *     }>
         * } $manifest
         */
        return $manifest;
    }

    /** @param array<mixed> $manifest */
    private function assertValidManifest(array $manifest): void
    {
        $categories = $manifest['categories'] ?? null;

        if (
            ($manifest['schema_version'] ?? null) !== self::SCHEMA_VERSION
            || ($manifest['source_payload_sha256'] ?? null) !== self::SOURCE_PAYLOAD_SHA256
            || ($manifest['root_category_count'] ?? null) !== self::ROOT_CATEGORY_COUNT
            || ($manifest['subcategory_count'] ?? null) !== self::SUBCATEGORY_COUNT
            || ! is_array($categories)
            || ! array_is_list($categories)
            || count($categories) !== self::ROOT_CATEGORY_COUNT
        ) {
            $this->invalidManifest();
        }

        $stableKeys = [];
        $slugs = [];
        $subcategoryCount = 0;

        foreach ($categories as $index => $category) {
            if (! is_array($category)) {
                $this->invalidManifest();
            }

            $number = $category['number'] ?? null;
            $stableKey = $category['stable_key'] ?? null;
            $slug = $category['slug'] ?? null;
            $subcategories = $category['subcategories'] ?? null;

            if (
                $number !== $index + 1
                || ! $this->isStableKey($stableKey)
                || ! $this->isSlug($slug, allowHierarchy: false)
                || ! $this->isNonEmptyString($category['name'] ?? null)
                || ! $this->isNonEmptyString($category['purpose'] ?? null)
                || ! $this->isNonEmptyString($category['icon'] ?? null)
                || ! $this->isNonEmptyString($category['source'] ?? null)
                || ! is_array($subcategories)
                || ! array_is_list($subcategories)
            ) {
                $this->invalidManifest();
            }

            $this->assertUnique($stableKey, $stableKeys);
            $this->assertUnique($slug, $slugs);

            foreach ($subcategories as $subcategory) {
                if (! is_array($subcategory)) {
                    $this->invalidManifest();
                }

                $childStableKey = $subcategory['stable_key'] ?? null;
                $childSlug = $subcategory['slug'] ?? null;

                if (
                    ! $this->isStableKey($childStableKey)
                    || ! str_starts_with($childStableKey, $stableKey.'.')
                    || ! $this->isSlug($childSlug, allowHierarchy: true)
                    || ! str_starts_with($childSlug, $slug.'/')
                    || ! $this->isNonEmptyString($subcategory['name'] ?? null)
                ) {
                    $this->invalidManifest();
                }

                $this->assertUnique($childStableKey, $stableKeys);
                $this->assertUnique($childSlug, $slugs);
                $subcategoryCount++;
            }
        }

        if ($subcategoryCount !== self::SUBCATEGORY_COUNT) {
            $this->invalidManifest();
        }
    }

    private function isStableKey(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/\Aforum\.[a-z0-9]+(?:[.-][a-z0-9]+)*\z/', $value) === 1;
    }

    private function isSlug(mixed $value, bool $allowHierarchy): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $pattern = $allowHierarchy
            ? '/\A[a-z0-9]+(?:-[a-z0-9]+)*\/[a-z0-9]+(?:-[a-z0-9]+)*\z/'
            : '/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/';

        return preg_match($pattern, $value) === 1;
    }

    private function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    /** @param array<string, true> $seen */
    private function assertUnique(string $value, array &$seen): void
    {
        if (isset($seen[$value])) {
            $this->invalidManifest();
        }

        $seen[$value] = true;
    }

    private function invalidManifest(): never
    {
        throw new RuntimeException(__('messages.the_forum_category_manifest_must_contain_all_44_root_categories'));
    }
}
