<?php

declare(strict_types=1);

use App\Data\RegisterForForumEventData;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Models\ForumEvent;
use App\Models\User;
use App\Services\ForumEventRegistrationService;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$arguments = $argv;
array_shift($arguments);
$database = (string) array_shift($arguments);
$mode = (string) array_shift($arguments);

foreach ([
    'APP_ENV' => 'testing',
    'CACHE_STORE' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $database,
    'DB_TRANSACTION_MODE' => 'IMMEDIATE',
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

if ($mode === 'prepare') {
    $participants = User::factory()->count(2)->create();
    $event = ForumEvent::factory()->withCapacity(1)->create([
        'waitlist_enabled' => true,
    ]);

    fwrite(STDOUT, json_encode([
        'event_id' => $event->id,
        'emails' => $participants->pluck('email')->values()->all(),
    ], JSON_THROW_ON_ERROR));

    exit(0);
}

[$eventId, $email, $operationKey, $readyPath, $barrierPath] = $arguments;
file_put_contents($readyPath, 'ready');
$deadline = microtime(true) + 15;

while (! is_file($barrierPath) && microtime(true) < $deadline) {
    usleep(10_000);
}

if (! is_file($barrierPath)) {
    fwrite(STDERR, 'The meetup race barrier was not released.');
    exit(2);
}

try {
    $actor = User::query()->where('email', $email)->firstOrFail();
    $event = ForumEvent::query()->findOrFail((int) $eventId);
    $registration = $application->make(ForumEventRegistrationService::class)->register(
        $actor,
        $event,
        new RegisterForForumEventData(
            attendanceFormat: ForumEventFormat::Physical,
            guestCount: 0,
            petProfileId: null,
            requirementsNote: null,
            photoConsent: ForumEventPhotoConsent::AskFirst,
            requirementsAccepted: true,
            idempotencyKey: $operationKey,
            petProfileIds: [],
        ),
    );

    fwrite(STDOUT, json_encode([
        'id' => $registration->id,
        'status' => $registration->status->value,
    ], JSON_THROW_ON_ERROR));
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class.': '.$exception->getMessage());
    exit(1);
}
