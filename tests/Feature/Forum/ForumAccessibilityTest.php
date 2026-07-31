<?php

declare(strict_types=1);

use App\Actions\PrepareTopicData;
use App\Data\PreparedTopicData;
use App\Models\ForumTopic;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** @param array<string, mixed> $overrides */
function accessibleForumTopicPayload(array $overrides = []): array
{
    return [
        'type' => 'question',
        'category' => 'behavior',
        'title' => 'How can I make this forum topic accessible to everyone?',
        'body' => 'This detailed topic explains the context, the relevant observations, and the specific outcome needed from other community members.',
        'visibility' => 'public',
        'comment_policy' => 'registered',
        'language' => 'en',
        'intent' => 'publish',
        ...$overrides,
    ];
}

test('topic media requires meaningful text alternatives', function () {
    Storage::fake('public');

    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), accessibleForumTopicPayload([
            'photos' => [UploadedFile::fake()->image('animal.jpg', 800, 600)],
        ]))
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors('photo_alt');

    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), accessibleForumTopicPayload([
            'photo_alt' => 'A dog waits beside an open carrier.',
            'video' => UploadedFile::fake()->create('routine.mp4', 512, 'video/mp4'),
        ]))
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors('video_transcript');

    expect(ForumTopic::query()->count())->toBe(0);
});

test('topic captions are content validated and require a matching video', function () {
    Storage::fake('public');

    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), accessibleForumTopicPayload([
            'video_captions' => UploadedFile::fake()->createWithContent(
                'captions.vtt',
                "WEBVTT\n\n00:00.000 --> 00:02.000\nDescription",
            ),
            'video_caption_locale' => 'en',
        ]))
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors('video_captions');

    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), accessibleForumTopicPayload([
            'photo_alt' => 'A dog waits beside an open carrier.',
            'video_transcript' => 'A quiet room followed by the carrier door opening.',
            'video' => UploadedFile::fake()->create('routine.mp4', 512, 'video/mp4'),
            'video_captions' => UploadedFile::fake()->createWithContent(
                'captions.vtt',
                "NOT-WEBVTT\n\n00:00.000 --> 00:02.000\nDescription",
            ),
            'video_caption_locale' => 'en',
        ]))
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors('video_captions');

    $this->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), accessibleForumTopicPayload([
            'photo_alt' => 'A dog waits beside an open carrier.',
            'video_transcript' => 'A quiet room followed by the carrier door opening.',
            'video' => UploadedFile::fake()->create('routine.mp4', 512, 'video/mp4'),
            'video_captions' => UploadedFile::fake()->createWithContent(
                'captions.vtt',
                "WEBVTT\n\nNOTE Missing cue timing\n",
            ),
            'video_caption_locale' => 'en',
        ]))
        ->assertRedirect(route('forum.topics.create'))
        ->assertSessionHasErrors('video_captions');

    expect(ForumTopic::query()->count())->toBe(0);
});

test('valid WebVTT captions and escaped transcripts are stored and rendered', function () {
    Storage::fake('public');
    $transcript = 'A bell sounds. <script>alert("unsafe")</script> Scout enters the carrier.';

    $response = $this->post(route('forum.topics.store'), accessibleForumTopicPayload([
        'photo_alt' => 'Scout stands beside an open carrier while a person points to a blanket.',
        'video_transcript' => $transcript,
        'video' => UploadedFile::fake()->create('routine.mp4', 512, 'video/mp4'),
        'video_captions' => UploadedFile::fake()->createWithContent(
            'private-address.vtt',
            "WEBVTT\n\n00:00.000 --> 00:02.000\nA bell sounds.\n",
        ),
        'video_caption_locale' => 'en',
    ]));

    $topic = ForumTopic::query()->firstOrFail();
    $media = $topic->media[0];

    $response->assertRedirect(route('forum.topics.show', $topic));
    expect($media)
        ->toMatchArray([
            'type' => 'video',
            'alt' => 'Scout stands beside an open carrier while a person points to a blanket.',
            'transcript' => $transcript,
            'caption_locale' => 'en',
        ])
        ->and($media['path'])->toStartWith('forum/videos/')
        ->and($media['caption_path'])->toStartWith('forum/captions/')
        ->and($media['caption_path'])->not->toContain('private-address');

    Storage::disk('public')->assertExists($media['path']);
    Storage::disk('public')->assertExists($media['caption_path']);

    $show = $this->get(route('forum.topics.show', $topic))->assertOk();
    $xpath = responseXPath($show);

    expect($xpath->query('//video[@controls and @aria-describedby]')->length)->toBe(1)
        ->and($xpath->query('//video/track[@kind="captions"][@srclang="en"][@default]')->length)->toBe(1)
        ->and($xpath->query('//figcaption[@id="topic-media-description-0"]//summary')->length)->toBe(1)
        ->and($xpath->query('//figcaption[@id="topic-media-description-0"]//script')->length)->toBe(0);

    $show->assertSee($transcript);
});

test('new media files can be compensated without deleting existing media', function () {
    Storage::fake('public');
    Storage::disk('public')->put('forum/images/existing.webp', 'existing');
    Storage::disk('public')->put('forum/images/new.webp', 'new-image');
    Storage::disk('public')->put('forum/captions/new.vtt', "WEBVTT\n");

    resolve(PrepareTopicData::class)->discardNewMedia(new PreparedTopicData(
        attributes: [],
        newMediaPaths: [
            'forum/images/new.webp',
            'forum/captions/new.vtt',
        ],
    ));

    Storage::disk('public')->assertExists('forum/images/existing.webp');
    Storage::disk('public')->assertMissing('forum/images/new.webp');
    Storage::disk('public')->assertMissing('forum/captions/new.vtt');
});

test('legacy topic media receives a localized non-empty text alternative', function () {
    Storage::fake('public');
    Storage::disk('public')->put('forum/images/legacy.webp', 'legacy-image');

    $topic = ForumTopic::factory()->create([
        'media' => [[
            'type' => 'image',
            'path' => 'forum/images/legacy.webp',
            'alt' => '',
            'sensitive' => false,
        ]],
    ]);

    $response = $this->get(route('forum.topics.show', $topic))->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query(
        '//img[@alt="Forum media shared by the topic author."]',
    )->length)->toBe(1);
});

test('forum validation responses expose one focusable complete error summary', function () {
    $response = $this->followingRedirects()
        ->from(route('forum.topics.create'))
        ->post(route('forum.topics.store'), accessibleForumTopicPayload([
            'title' => 'Too short',
            'body' => 'Too short',
        ]))
        ->assertOk();
    $xpath = responseXPath($response);

    expect($xpath->query(
        '//*[@data-forum-error-summary][@role="alert"][@aria-live="assertive"][@tabindex="-1"]',
    )->length)->toBe(1)
        ->and($xpath->query('//*[@data-forum-error-summary]//*[@data-error-field="title"]')->length)
        ->toBeGreaterThan(0)
        ->and($xpath->query('//*[@data-forum-error-summary]//*[@data-error-field="body"]')->length)
        ->toBeGreaterThan(0);
});
