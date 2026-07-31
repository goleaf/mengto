<?php

declare(strict_types=1);

use App\Actions\RegisterUser;
use App\Enums\UserStatus;
use App\Livewire\Auth\Login;
use App\Models\AuditLog;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('production sessions enforce secure browser only cookies', function () {
    $probe = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$valid = config('session.secure') === true
    && config('session.http_only') === true
    && in_array(config('session.same_site'), ['lax', 'strict'], true)
    && config('session.serialization') === 'json';

exit($valid ? 0 : 1);
PHP;

    $result = Process::path(base_path())
        ->env([
            'APP_ENV' => 'production',
            'SESSION_SECURE_COOKIE' => 'false',
            'SESSION_HTTP_ONLY' => 'false',
            'SESSION_SAME_SITE' => 'none',
        ])
        ->run([PHP_BINARY, '-r', $probe]);

    expect($result->successful(), $result->errorOutput().$result->output())->toBeTrue();
});

test('login throttles repeated credential attempts by normalized email and ip', function () {
    $this->freezeTime();
    auth()->logout();

    foreach (range(1, 5) as $attempt) {
        Livewire::test(Login::class)
            ->set('form.email', mb_strtoupper($this->authenticatedUser->email))
            ->set('form.password', 'invalid-password-'.$attempt)
            ->call('authenticate')
            ->assertHasErrors(['form.email']);
    }

    Livewire::test(Login::class)
        ->set('form.email', $this->authenticatedUser->email)
        ->set('form.password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['form.email'])
        ->assertSee(__('auth.login.throttled', ['seconds' => 60]));

    $this->assertGuest();
});

test('registration ignores privilege and identity fields outside its allow list', function () {
    Notification::fake();

    $user = app(RegisterUser::class)->handle([
        'name' => 'Security Test',
        'email' => 'security@example.test',
        'password' => 'Secure-Paw-2026',
        'actor_key' => 'attacker-controlled-key',
        'is_admin' => true,
        'status' => UserStatus::Blocked->value,
        'email_verified_at' => now(),
        'locale' => 'unsupported',
        'timezone' => 'Pacific/Honolulu',
    ]);
    $user->refresh();

    expect($user)
        ->actor_key->not->toBe('attacker-controlled-key')
        ->is_admin->toBeFalse()
        ->status->toBe(UserStatus::Active)
        ->email_verified_at->toBeNull()
        ->locale->toBe('en')
        ->timezone->toBe('UTC');
});

test('private medical upload rejects executable content disguised as an image', function () {
    Storage::fake('local');
    $record = MedicalRecord::factory()->create(['owner_key' => 'mia-carter']);
    $temporaryPath = tempnam(sys_get_temp_dir(), 'security-upload-');

    if ($temporaryPath === false) {
        throw new RuntimeException('Unable to create the upload security fixture.');
    }

    file_put_contents($temporaryPath, '<?php echo "unsafe"; ?>');
    $upload = new UploadedFile(
        $temporaryPath,
        'shell.php.jpg',
        'image/jpeg',
        null,
        true,
    );

    try {
        $this->post(route('medical-records.documents.store', $record), [
            'title' => 'Disguised executable',
            'document_type' => 'other',
            'source_type' => 'owner',
            'source_name' => 'Security test',
            'document' => $upload,
        ])->assertSessionHasErrors('document');
    } finally {
        if (file_exists($temporaryPath)) {
            unlink($temporaryPath);
        }
    }

    expect(MedicalDocument::query()->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('nested private document identifiers fail closed without an existence oracle', function () {
    Storage::fake('local');
    $ownedRecord = MedicalRecord::factory()->create(['owner_key' => 'mia-carter']);
    $foreignRecord = MedicalRecord::factory()->create(['owner_key' => 'another-owner']);
    $foreignDocument = MedicalDocument::factory()->for($foreignRecord)->create([
        'file_path' => 'medical-records/foreign/private.pdf',
    ]);
    Storage::disk('local')->put($foreignDocument->file_path, 'private document');

    expect(route('medical-records.documents.download', [
        $ownedRecord,
        $foreignDocument,
    ]))->toContain('/documents/'.$foreignDocument->getRouteKey())
        ->and(RouteFacade::getRoutes()
            ->getByName('medical-records.documents.download')
            ?->enforcesScopedBindings())
        ->toBeTrue();

    $this->get(route('medical-records.documents.download', [
        $ownedRecord,
        $foreignDocument,
    ]))->assertNotFound();

    expect($foreignDocument->refresh()->download_count)->toBe(0)
        ->and(AuditLog::query()->where('action', 'medical-document.downloaded')->count())->toBe(0);
});
