<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\TaxonomySnapshotAnalysis;
use App\Data\TaxonomySnapshotChunk;
use App\Models\TaxonImport;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

final class TaxonomySnapshotReader
{
    public function analyze(string $disk, string $path): TaxonomySnapshotAnalysis
    {
        $this->assertSafePath($path);
        $filesystem = Storage::disk($disk);

        if (! $filesystem->exists($path)) {
            throw new InvalidArgumentException(__('messages.the_taxonomy_snapshot_does_not_exist_on_the_configured_d_f88553b64d'));
        }

        $delimiter = str_ends_with(mb_strtolower($path), '.tsv')
            || str_ends_with(mb_strtolower($path), '.txt')
                ? "\t"
                : ',';
        $checksum = $this->checksum($filesystem, $path);
        $stream = $this->openStream($filesystem, $path);

        try {
            $headers = $this->readHeaders($stream, $delimiter);
            $columnMap = $this->mapColumns($headers);
            $rowCount = 0;
            $warningCount = 0;
            $sourceIdIndex = $columnMap['source_record_id'];
            $scientificNameIndex = $columnMap['scientific_name'];

            while (($row = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $rowCount++;

                if (
                    trim((string) ($row[$sourceIdIndex] ?? '')) === ''
                    || trim((string) ($row[$scientificNameIndex] ?? '')) === ''
                ) {
                    $warningCount++;
                }
            }
        } finally {
            fclose($stream);
        }

        if ($rowCount === 0) {
            throw new InvalidArgumentException(__('messages.the_taxonomy_snapshot_contains_no_data_rows_115b837cf0'));
        }

        return new TaxonomySnapshotAnalysis(
            disk: $disk,
            path: $path,
            checksum: $checksum,
            delimiter: $delimiter,
            headers: $headers,
            columnMap: $columnMap,
            rowCount: $rowCount,
            warningCount: $warningCount,
        );
    }

    public function readChunk(TaxonImport $import, int $limit): TaxonomySnapshotChunk
    {
        $metadata = $import->metadata ?? [];
        $disk = (string) ($metadata['snapshot_disk'] ?? '');
        $path = (string) ($metadata['snapshot_path'] ?? '');
        $delimiter = (string) ($metadata['delimiter'] ?? ',');
        $columnMap = $metadata['column_map'] ?? null;

        if ($disk === '' || $path === '' || ! is_array($columnMap)) {
            throw new RuntimeException(__('messages.the_taxonomy_import_has_no_valid_snapshot_metadata_b246d42c2c'));
        }

        $this->assertSafePath($path);
        $stream = $this->openStream(Storage::disk($disk), $path);
        $resume = json_decode((string) ($import->resume_token ?? ''), true);
        $offset = is_array($resume) ? (int) ($resume['offset'] ?? 0) : 0;
        $sourceRow = is_array($resume) ? (int) ($resume['source_row'] ?? 1) : 1;

        try {
            if ($offset > 0) {
                if (fseek($stream, $offset) !== 0) {
                    throw new RuntimeException(__('messages.the_taxonomy_snapshot_stream_cannot_resume_at_the_saved__bbb2c6fe6d'));
                }
            } else {
                fgetcsv($stream, null, $delimiter, '"', '');
            }

            $rows = [];

            while (count($rows) < $limit) {
                $row = fgetcsv($stream, null, $delimiter, '"', '');

                if ($row === false) {
                    break;
                }

                $sourceRow++;

                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $mapped = [];

                foreach ($columnMap as $canonical => $index) {
                    $mapped[(string) $canonical] = is_int($index)
                        ? $this->nullableTrim($row[$index] ?? null)
                        : null;
                }

                $mapped['_source_row'] = (string) $sourceRow;
                $rows[] = $mapped;
            }

            $nextOffset = ftell($stream);

            if ($nextOffset === false) {
                throw new RuntimeException(__('messages.the_taxonomy_snapshot_stream_position_could_not_be_read_d1cbefca75'));
            }

            $isComplete = feof($stream);
        } finally {
            fclose($stream);
        }

        return new TaxonomySnapshotChunk(
            rows: $rows,
            nextOffset: $nextOffset,
            lastRow: $sourceRow,
            isComplete: $isComplete,
        );
    }

    private function assertSafePath(string $path): void
    {
        $directory = trim((string) config('taxonomy.snapshot_directory'), '/');
        $normalized = str_replace('\\', '/', trim($path));

        if (
            $directory === ''
            || $normalized === ''
            || str_starts_with($normalized, '/')
            || str_contains($normalized, '../')
            || $normalized === '..'
            || ! str_starts_with($normalized, $directory.'/')
        ) {
            throw new InvalidArgumentException(__('messages.taxonomy_snapshots_must_use_a_relative_path_inside_the_c_c50d8fce0a'));
        }
    }

    /**
     * @param  resource  $stream
     * @return list<string>
     */
    private function readHeaders($stream, string $delimiter): array
    {
        $headers = fgetcsv($stream, null, $delimiter, '"', '');

        if (! is_array($headers)) {
            throw new InvalidArgumentException(__('messages.the_taxonomy_snapshot_header_is_missing_fb4a079058'));
        }

        return array_values(array_map(
            static fn (mixed $header): string => trim(
                (string) $header,
                "\xEF\xBB\xBF \t\n\r\0\x0B",
            ),
            $headers,
        ));
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, int|null>
     */
    private function mapColumns(array $headers): array
    {
        $positions = array_flip($headers);
        $map = [];

        /** @var array<string, list<string>> $candidateSets */
        $candidateSets = config('taxonomy.column_candidates', []);

        foreach ($candidateSets as $canonical => $candidates) {
            $map[$canonical] = null;

            foreach ($candidates as $candidate) {
                if (array_key_exists($candidate, $positions)) {
                    $map[$canonical] = (int) $positions[$candidate];
                    break;
                }
            }
        }

        /** @var list<string> $required */
        $required = config('taxonomy.required_columns', []);
        $missing = array_values(array_filter(
            $required,
            static fn (string $column): bool => ! is_int($map[$column] ?? null),
        ));

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'The taxonomy snapshot is missing required columns: '.implode(', ', $missing).'.',
            );
        }

        return $map;
    }

    private function checksum(Filesystem $filesystem, string $path): string
    {
        $stream = $this->openStream($filesystem, $path);

        try {
            $context = hash_init('sha256');
            hash_update_stream($context, $stream);

            return hash_final($context);
        } finally {
            fclose($stream);
        }
    }

    /**
     * @return resource
     */
    private function openStream(Filesystem $filesystem, string $path)
    {
        $stream = $filesystem->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException(__('messages.the_taxonomy_snapshot_could_not_be_opened_4eeadbaac0'));
        }

        return $stream;
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        return array_all(
            $row,
            static fn (mixed $value): bool => trim((string) $value) === '',
        );
    }

    private function nullableTrim(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
