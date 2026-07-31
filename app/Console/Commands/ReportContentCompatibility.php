<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContentCompatibilityReport;
use Illuminate\Console\Command;

final class ReportContentCompatibility extends Command
{
    protected $signature = 'content:compatibility-report {--json : Emit a machine-readable report}';

    protected $description = 'Report canonical and preserved legacy content without importing private prototype data';

    public function handle(ContentCompatibilityReport $report): int
    {
        $counts = $report->generate();

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($counts, JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->table(
            [__('content.compatibility.source'), __('content.compatibility.records')],
            collect($counts)->map(
                static fn (int $count, string $source): array => [
                    __("content.compatibility.sources.{$source}"),
                    $count,
                ],
            )->values()->all(),
        );
        $this->components->warn(__('content.compatibility.no_import'));

        return self::SUCCESS;
    }
}
