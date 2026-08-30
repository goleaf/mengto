<?php

declare(strict_types=1);

use App\Services\PortalMediaResponse;
use App\Services\PortalMediaUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('guest cannot request portal media or reveal whether it exists', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('forum/images/private.webp', 'private-image');
    auth()->logout();
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('portal-media.show', ['path' => 'forum/images/private.webp']))
        ->assertRedirect(route('login'));

    expect(DB::getQueryLog())->toBeEmpty();
});

test('verified active account can render allowlisted portal media inline', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('forum/images/topic.webp', 'topic-image');

    $this->get(route('portal-media.show', ['path' => 'forum/images/topic.webp']))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'image/webp')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertStreamedContent('topic-image');
});

test('portal media rejects traversal absolute cross domain missing and unsupported paths', function (
    string $path,
): void {
    Storage::fake('public');
    Storage::disk('public')->put('medical-records/42/report.pdf', 'private-report');
    Storage::disk('public')->put('forum/images/file.svg', '<svg></svg>');

    expect(fn () => app(PortalMediaResponse::class)->inline($path))
        ->toThrow(NotFoundHttpException::class);
})->with([
    'parent segment' => 'forum/images/../../medical-records/42/report.pdf',
    'windows traversal' => 'forum\\images\\..\\..\\medical-records\\42\\report.pdf',
    'absolute path' => '/etc/passwd',
    'unapproved private domain' => 'medical-records/42/report.pdf',
    'missing file' => 'forum/images/missing.webp',
    'unsupported active content' => 'forum/images/file.svg',
]);

test('portal media rejects symbolic links that escape their allowlisted directory', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('medical-records/42/private.webp', 'private-image');
    Storage::disk('public')->makeDirectory('forum/images');

    $created = symlink(
        Storage::disk('public')->path('medical-records/42/private.webp'),
        Storage::disk('public')->path('forum/images/linked.webp'),
    );

    expect($created)->toBeTrue()
        ->and(fn () => app(PortalMediaResponse::class)->inline('forum/images/linked.webp'))
        ->toThrow(NotFoundHttpException::class);
});

test('portal media urls convert stored paths and legacy storage urls', function (): void {
    $urls = app(PortalMediaUrl::class);
    $expected = route('portal-media.show', ['path' => 'forum/images/topic.webp']);

    expect($urls->for('forum/images/topic.webp'))->toBe($expected)
        ->and($urls->for(url('/storage/forum/images/topic.webp')))->toBe($expected)
        ->and($urls->for($expected))->toBe($expected)
        ->and($urls->for('https://images.example.test/topic.webp'))
        ->toBe('https://images.example.test/topic.webp')
        ->and(config('filesystems.links'))->toBe([])
        ->and(is_link(public_path('storage')))->toBeFalse();
});
