<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('production host and authentication mail defaults fail closed', function (): void {
    $bootstrap = File::get(base_path('bootstrap/app.php'));
    $mail = File::get(config_path('mail.php'));
    $provider = File::get(app_path('Providers/AppServiceProvider.php'));

    expect($bootstrap)->toContain('$middleware->trustHosts();')
        ->and($mail)->toContain("env('MAIL_MAILER', 'smtp')")
        ->and($mail)->toContain("'timeout' => 10")
        ->and($provider)->toContain("['array', 'log']")
        ->and($provider)->toContain('Production authentication mail must not use')
        ->and(File::get(base_path('.env.example')))->toContain("MAIL_MAILER=smtp\n");

    expect(File::get(base_path('.env.example')))->not->toContain('MAIL_PASSWORD=null');
});
