<?php

declare(strict_types=1);

use App\Enums\PhotoReactionType;
use App\Models\PhotoAsset;
use App\Models\PhotoComment;
use App\Models\PhotoReaction;
use App\Models\User;
use App\Services\PhotoInteractionState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

/** @return array<string, mixed> */
function canonicalPhotoFixture(string $postKey, int $position): array
{
    $photoKey = "{$postKey}-photo-{$position}";

    return [
        'type' => 'image',
        'photo_key' => $photoKey,
        'post_key' => $postKey,
        'position' => $position,
        'image' => '/images/places/park-primary-lg.jpg',
        'image_small' => '/images/places/park-primary-sm.jpg',
        'image_medium' => '/images/places/park-primary-md.jpg',
        'viewer_srcset' => '/images/places/park-primary-sm.jpg 576w, /images/places/park-primary-md.jpg 900w, /images/places/park-primary-lg.jpg 1200w',
        'alt' => "Canonical publication photo {$position}",
        'caption' => "Authenticated member photo {$position}",
        'avatar' => '/images/places/park-primary-sm.jpg',
        'author' => 'Test Member',
        'represented' => 'Test Member',
        'post_url' => route('preview.feed'),
        'reaction_options' => [
            'like' => __('messages.like'),
            'love' => __('messages.love'),
            'support' => __('messages.support'),
        ],
        'reaction_items' => [
            [
                'value' => 'like',
                'label' => __('messages.like'),
                'icon' => 'paw-print',
                'count' => 0,
                'selected' => false,
            ],
        ],
        'selected_reaction' => null,
        'selected_reaction_label' => null,
        'reaction_total' => 0,
        'comments' => [],
        'comment_count' => 0,
        'comment_idempotency_key' => Str::lower((string) Str::ulid()),
    ];
}

test('prepared publication photos expose one responsive viewer and social panel per image', function () {
    expect(Route::has('photos.interactions.store'))->toBeTrue();
    View::share('errors', new ViewErrorBag);

    $media = array_map(
        static fn (int $position): array => canonicalPhotoFixture('canonical-publication', $position),
        [1, 2, 3],
    );
    $html = Blade::render('<x-post-media-gallery :media="$media" />', [
        'media' => $media,
        'errors' => new ViewErrorBag,
    ]);
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $triggers = $xpath->query('//a[@data-photo-trigger]');
    $templates = $xpath->query('//template[@data-photo-panel-template]');

    expect($triggers->length)->toBeGreaterThan(0)
        ->and($templates->length)->toBe($triggers->length)
        ->and($xpath->query('//a[@data-photo-trigger and @data-pswp-width="1200" and @data-pswp-height="900" and @data-pswp-srcset]')->length)
        ->toBe($triggers->length)
        ->and($xpath->query('//form[contains(@class, "photo-social__composer")]//input[@name="idempotency_key"]')->length)
        ->toBe($triggers->length)
        ->and($xpath->query('//a[@data-photo-key="canonical-publication-photo-1"]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-photo-key="canonical-publication-photo-2"]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-photo-key="canonical-publication-photo-3"]')->length)->toBe(1);
});

test('photo viewer keeps progressive and Livewire navigation lifecycle hooks', function () {
    $source = file_get_contents(resource_path('js/photo-viewer.js'));

    expect($source)->toBeString()
        ->toContain("import('photoswipe')")
        ->toContain("document.addEventListener('livewire:navigating', destroyPhotoViewer)")
        ->toContain("document.addEventListener('livewire:navigated', initializePhotoViewer)")
        ->toContain('lightbox.loadAndOpen');
});

test('reactions are stored independently for each photo', function () {
    $state = app(PhotoInteractionState::class);
    $first = canonicalPhotoFixture('canonical-publication', 1);
    $second = canonicalPhotoFixture('canonical-publication', 2);

    expect($state->setReaction($second, 'love'))->toBe('love');

    expect($state->reaction('canonical-publication-photo-2'))->toBe('love')
        ->and($state->reaction('canonical-publication-photo-1'))->toBeNull()
        ->and(PhotoReaction::query()
            ->whereBelongsTo($this->authenticatedUser)
            ->whereHas('photoAsset', fn ($query) => $query
                ->where('key', 'canonical-publication-photo-2'))
            ->value('reaction'))->toBe(PhotoReactionType::Love);

    expect($state->setReaction($second, 'love'))->toBeNull();

    expect(PhotoReaction::query()->count())->toBe(0);

    $this->actingAs($this->authenticatedUser);
    app(PhotoInteractionState::class)->setReaction($first, 'support');
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);
    app(PhotoInteractionState::class)->setReaction($first, 'love');

    $this->actingAs($this->authenticatedUser);
    $sharedState = app(PhotoInteractionState::class);

    expect($sharedState->reaction('canonical-publication-photo-1'))->toBe('support')
        ->and($sharedState->reactionCounts('canonical-publication-photo-1')['support'])->toBe(1)
        ->and($sharedState->reactionCounts('canonical-publication-photo-1')['love'])->toBe(1);
});

test('comments are shared, escaped, and stored for only the selected photo', function () {
    View::share('errors', new ViewErrorBag);
    $commentKey = Str::lower((string) Str::ulid());
    $photo = canonicalPhotoFixture('canonical-publication', 3);
    $state = app(PhotoInteractionState::class);

    $state->addComment($photo, '<script>alert("no")</script> Calm ending to the walk.', $commentKey);
    $state->addComment($photo, '<script>alert("no")</script> Calm ending to the walk.', $commentKey);

    $otherUser = User::factory()->create(['name' => 'Alex Rivera']);
    $this->actingAs($otherUser);
    app(PhotoInteractionState::class)->addComment(
        $photo,
        'The light in this one is wonderful.',
        Str::lower((string) Str::ulid()),
    );

    $this->actingAs($this->authenticatedUser);
    $freshState = app(PhotoInteractionState::class);
    $comments = $freshState->comments('canonical-publication-photo-3');

    expect($comments)->toHaveCount(2)
        ->and($comments[0]['body'])
        ->toBe('<script>alert("no")</script> Calm ending to the walk.')
        ->and($comments[1]['author'])->toBe('Alex Rivera')
        ->and(PhotoComment::query()->count())->toBe(2)
        ->and($freshState->comments('canonical-publication-photo-2'))->toBe([]);

    $html = Blade::render('<x-photo-social-panel :photo="$photo" />', [
        'photo' => [
            ...$photo,
            'comments' => $comments,
            'comment_count' => count($comments),
        ],
        'errors' => new ViewErrorBag,
    ]);

    expect($html)
        ->toContain('&lt;script&gt;alert(&quot;no&quot;)&lt;/script&gt; Calm ending to the walk.')
        ->toContain('The light in this one is wonderful.')
        ->not->toContain('<script>alert("no")</script>');
});

test('photo interaction presentation uses a bounded query count', function () {
    $asset = PhotoAsset::factory()->create([
        'key' => 'canonical-publication-photo-1',
        'post_key' => 'canonical-publication',
        'position' => 1,
    ]);
    PhotoComment::factory()->for($asset, 'photoAsset')->count(3)->create();
    PhotoReaction::factory()->for($asset, 'photoAsset')->create([
        'user_id' => $this->authenticatedUser->id,
        'reaction' => 'support',
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $state = app(PhotoInteractionState::class);
    $state->load([
        'canonical-publication-photo-1',
        'canonical-publication-photo-2',
        'canonical-publication-photo-3',
    ]);

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBe(4)
        ->and($state->comments('canonical-publication-photo-1'))->toHaveCount(3)
        ->and($state->reaction('canonical-publication-photo-1'))->toBe('support');
});

test('photo interaction rejects unknown photos and guests', function () {
    $this->from(route('preview.feed'))->post(route('photos.interactions.store'), [
        'action' => 'set-reaction',
        'photo' => 'unknown-photo-1',
        'reaction' => 'like',
    ])
        ->assertRedirect(route('preview.feed'))
        ->assertSessionHasErrors('photo');

    Auth::logout();

    $this->post(route('photos.interactions.store'), [
        'action' => 'create-comment',
        'photo' => 'canonical-publication-photo-1',
        'body' => 'Guest comment',
        'idempotency_key' => Str::lower((string) Str::ulid()),
    ])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->blocked()->create())
        ->post(route('photos.interactions.store'), [
            'action' => 'set-reaction',
            'photo' => 'canonical-publication-photo-1',
            'reaction' => 'like',
        ])
        ->assertRedirect(route('login'));

    expect(PhotoReaction::query()->count())->toBe(0);
});
