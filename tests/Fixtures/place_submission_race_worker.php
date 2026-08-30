<?php

declare(strict_types=1);

use App\Actions\SubmitPlaceSubmission;
use App\Data\SubmitPlaceData;
use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

[$script, $database, $email, $operationKey, $readyPath, $barrierPath] = $argv;

foreach ([
    'APP_ENV' => 'testing',
    'CACHE_STORE' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $database,
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'array',
] as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}
putenv('DB_URL');
unset($_ENV['DB_URL'], $_SERVER['DB_URL']);

$application = require dirname(__DIR__, 2).'/bootstrap/app.php';
$application->make(Kernel::class)->bootstrap();

file_put_contents($readyPath, 'ready');
$deadline = microtime(true) + 15;

while (! is_file($barrierPath) && microtime(true) < $deadline) {
    usleep(10_000);
}

if (! is_file($barrierPath)) {
    fwrite(STDERR, 'The race barrier was not released.');
    exit(2);
}

try {
    $actor = User::query()->where('email', $email)->firstOrFail();
    $submission = $application->make(SubmitPlaceSubmission::class)->handle(
        $actor,
        new SubmitPlaceData(
            name: 'Concurrent Community Clinic',
            type: PlaceType::VeterinaryClinic,
            catalogCategory: 'vet',
            source: PlaceSubmissionSource::PersonalVisit,
            sourceReference: 'https://example.test/concurrent-clinic',
            relationshipToPlace: 'visitor',
            locationPrecision: PlaceLocationPrecision::PublicPoint,
            locale: 'en',
            publicRegion: 'Vilnius',
            publicAddress: 'Concurrent Street 10, Vilnius',
            publicLatitude: '54.700100',
            publicLongitude: '25.300100',
            exactAddress: null,
            exactLatitude: null,
            exactLongitude: null,
            publicPhone: '+37061234000',
            publicEmail: 'concurrent-clinic@example.test',
            publicWebsite: 'https://example.test/concurrent-clinic',
            summary: 'A deterministic simultaneous-submission fixture.',
            facts: [
                'hours' => ['monday' => '08:00-20:00'],
                'services' => ['preventive-care'],
                'features' => ['step-free-entrance'],
            ],
            canonicalOrganizationId: null,
            observedAt: CarbonImmutable::now()->subDay(),
            consentVersion: 'places-submission-v1',
            consentGranted: true,
            idempotencyKey: $operationKey,
        ),
    );

    fwrite(STDOUT, json_encode([
        'id' => $submission->id,
        'status' => $submission->status->value,
    ], JSON_THROW_ON_ERROR));
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class.': '.$exception->getMessage());
    exit(1);
}
