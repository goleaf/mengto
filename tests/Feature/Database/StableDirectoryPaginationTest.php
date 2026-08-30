<?php

declare(strict_types=1);

use App\Services\CareJournalPresenter;
use App\Services\MedicalRecordPresenter;
use App\Services\SmartDevicePresenter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

test('private directory pagination uses an immutable id tie breaker', function (): void {
    $queries = [];
    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    app(CareJournalPresenter::class)->directory();
    app(MedicalRecordPresenter::class)->directory();
    app(SmartDevicePresenter::class)->directory();

    foreach (['care_journals', 'medical_records', 'smart_devices'] as $table) {
        $directoryQuery = collect($queries)->first(
            static fn (string $query): bool => str_contains($query, 'from "'.$table.'"')
                && str_contains($query, 'order by'),
        );

        expect($directoryQuery, $table)
            ->toBeString()
            ->toContain('order by "updated_at" desc, "id" desc');
    }
});
