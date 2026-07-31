<?php

declare(strict_types=1);

namespace App\Services;

use JsonException;
use RuntimeException;

final class ForumCategoryCatalog
{
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
        $path = resource_path('data/forum/categories.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read forum category manifest at {$path}.");
        }

        try {
            $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(__('messages.the_forum_category_manifest_is_invalid_json_36219f1d45'), previous: $exception);
        }

        if (
            ! is_array($manifest)
            || ($manifest['root_category_count'] ?? null) !== 44
            || ! is_array($manifest['categories'] ?? null)
            || count($manifest['categories']) !== 44
        ) {
            throw new RuntimeException(__('messages.the_forum_category_manifest_must_contain_all_44_root_cat_e313247f54'));
        }

        return $manifest;
    }
}
