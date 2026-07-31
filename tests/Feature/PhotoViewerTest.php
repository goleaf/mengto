<?php

declare(strict_types=1);

use App\Models\PhotoAsset;
use App\Models\PhotoComment;
use App\Models\PhotoReaction;
use App\Models\User;
use App\Services\PhotoInteractionState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

test('feed photos expose one responsive viewer and social panel per image', function () {
    expect(Route::has('photos.interactions.store'))->toBeTrue();

    $response = $this->get(route('home'));

    $response->assertSuccessful();

    $xpath = responseXPath($response);
    $triggers = $xpath->query('//a[@data-photo-trigger]');
    $templates = $xpath->query('//template[@data-photo-panel-template]');

    expect($triggers->length)->toBeGreaterThan(0)
        ->and($templates->length)->toBe($triggers->length)
        ->and($xpath->query('//a[@data-photo-trigger and @data-pswp-width="1200" and @data-pswp-height="900" and @data-pswp-srcset]')->length)
        ->toBe($triggers->length)
        ->and($xpath->query('//form[contains(@class, "photo-social__composer")]//input[@name="idempotency_key"]')->length)
        ->toBe($triggers->length)
        ->and($xpath->query('//a[@data-photo-key="scout-shaded-loop-photo-1"]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-photo-key="scout-shaded-loop-photo-2"]')->length)->toBe(1)
        ->and($xpath->query('//a[@data-photo-key="scout-shaded-loop-photo-3"]')->length)->toBe(1);
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
    $this->from(route('home'))->post(route('photos.interactions.store'), [
        'action' => 'set-reaction',
        'photo' => 'scout-shaded-loop-photo-2',
        'reaction' => 'love',
    ])
        ->assertRedirect(route('home'))
        ->assertSessionHas('feedback');

    $state = app(PhotoInteractionState::class);

    expect($state->reaction('scout-shaded-loop-photo-2'))->toBe('love')
        ->and($state->reaction('scout-shaded-loop-photo-1'))->toBeNull()
        ->and(PhotoReaction::query()
            ->whereBelongsTo($this->authenticatedUser)
            ->whereHas('photoAsset', fn ($query) => $query
                ->where('key', 'scout-shaded-loop-photo-2'))
            ->value('reaction'))->toBe('love');

    $this->from(route('home'))->post(route('photos.interactions.store'), [
        'action' => 'set-reaction',
        'photo' => 'scout-shaded-loop-photo-2',
        'reaction' => 'love',
    ])->assertSessionHas('feedback');

    expect(PhotoReaction::query()->count())->toBe(0);

    $this->actingAs($this->authenticatedUser)
        ->post(route('photos.interactions.store'), [
            'action' => 'set-reaction',
            'photo' => 'scout-shaded-loop-photo-1',
            'reaction' => 'support',
        ]);
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)
        ->post(route('photos.interactions.store'), [
            'action' => 'set-reaction',
            'photo' => 'scout-shaded-loop-photo-1',
            'reaction' => 'love',
        ]);

    $this->actingAs($this->authenticatedUser);
    $sharedState = app(PhotoInteractionState::class);

    expect($sharedState->reaction('scout-shaded-loop-photo-1'))->toBe('support')
        ->and($sharedState->reactionCounts('scout-shaded-loop-photo-1')['support'])->toBe(1)
        ->and($sharedState->reactionCounts('scout-shaded-loop-photo-1')['love'])->toBe(1);
});

test('comments are shared, escaped, and stored for only the selected photo', function () {
    $commentKey = Str::lower((string) Str::ulid());
    $commentPayload = [
        'action' => 'create-comment',
        'photo' => 'scout-shaded-loop-photo-3',
        'body' => '<script>alert("no")</script> Calm ending to the walk.',
        'idempotency_key' => $commentKey,
    ];

    $this->from(route('home'))->post(route('photos.interactions.store'), $commentPayload)
        ->assertRedirect(route('home'))
        ->assertSessionHas('feedback');
    $this->from(route('home'))->post(route('photos.interactions.store'), $commentPayload)
        ->assertRedirect(route('home'));

    $otherUser = User::factory()->create(['name' => 'Alex Rivera']);
    $this->actingAs($otherUser)
        ->from(route('home'))
        ->post(route('photos.interactions.store'), [
            'action' => 'create-comment',
            'photo' => 'scout-shaded-loop-photo-3',
            'body' => 'The light in this one is wonderful.',
            'idempotency_key' => Str::lower((string) Str::ulid()),
        ])
        ->assertRedirect(route('home'));

    $this->actingAs($this->authenticatedUser);
    $state = app(PhotoInteractionState::class);
    $comments = $state->comments('scout-shaded-loop-photo-3');

    expect($comments)->toHaveCount(2)
        ->and($comments[0]['body'])
        ->toBe('<script>alert("no")</script> Calm ending to the walk.')
        ->and($comments[1]['author'])->toBe('Alex Rivera')
        ->and(PhotoComment::query()->count())->toBe(2)
        ->and($state->comments('scout-shaded-loop-photo-2'))->toBe([]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('&lt;script&gt;alert(&quot;no&quot;)&lt;/script&gt; Calm ending to the walk.', false)
        ->assertSee('The light in this one is wonderful.')
        ->assertDontSee('<script>alert("no")</script>', false);
});

test('photo interaction presentation uses a bounded query count', function () {
    $asset = PhotoAsset::factory()->create([
        'key' => 'scout-shaded-loop-photo-1',
        'post_key' => 'scout-shaded-loop',
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
        'scout-shaded-loop-photo-1',
        'scout-shaded-loop-photo-2',
        'scout-shaded-loop-photo-3',
    ]);

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBe(4)
        ->and($state->comments('scout-shaded-loop-photo-1'))->toHaveCount(3)
        ->and($state->reaction('scout-shaded-loop-photo-1'))->toBe('support');
});

test('photo interaction rejects unknown photos and guests', function () {
    $this->from(route('home'))->post(route('photos.interactions.store'), [
        'action' => 'set-reaction',
        'photo' => 'unknown-photo-1',
        'reaction' => 'like',
    ])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('photo');

    Auth::logout();

    $this->post(route('photos.interactions.store'), [
        'action' => 'create-comment',
        'photo' => 'scout-shaded-loop-photo-1',
        'body' => 'Guest comment',
        'idempotency_key' => Str::lower((string) Str::ulid()),
    ])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->blocked()->create())
        ->post(route('photos.interactions.store'), [
            'action' => 'set-reaction',
            'photo' => 'scout-shaded-loop-photo-1',
            'reaction' => 'like',
        ])
        ->assertRedirect(route('login'));

    expect(PhotoReaction::query()->count())->toBe(0);
});
