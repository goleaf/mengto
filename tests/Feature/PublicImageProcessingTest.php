<?php

use App\Actions\StorePublicImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('public images are oriented scaled and encoded as webp', function () {
    Storage::fake('public');

    $path = app(StorePublicImage::class)->handle(
        UploadedFile::fake()->image('large-landscape.png', 3200, 2000),
        'test-images',
    );
    $size = getimagesizefromstring(Storage::disk('public')->get($path));

    expect($path)->toStartWith('test-images/')
        ->toEndWith('.webp')
        ->and($size)->not->toBeFalse()
        ->and($size[0])->toBe(2560)
        ->and($size[1])->toBe(1600)
        ->and($size['mime'])->toBe('image/webp');

    Storage::disk('public')->assertExists($path);
});

test('public image scaling never enlarges a smaller upload', function () {
    Storage::fake('public');

    $path = app(StorePublicImage::class)->handle(
        UploadedFile::fake()->image('small.jpg', 640, 480),
        'test-images',
    );
    $size = getimagesizefromstring(Storage::disk('public')->get($path));

    expect($size)->not->toBeFalse()
        ->and($size[0])->toBe(640)
        ->and($size[1])->toBe(480)
        ->and($size['mime'])->toBe('image/webp');
});

test('malformed image content fails with a localized validation error', function () {
    Storage::fake('public');

    try {
        app(StorePublicImage::class)->handle(
            UploadedFile::fake()->createWithContent('broken.jpg', 'not image bytes'),
            'test-images',
            'photo',
        );
    } catch (ValidationException $exception) {
        expect($exception->errors())
            ->toHaveKey('photo')
            ->and($exception->errors()['photo'][0])
            ->toBe(__('messages.public_image_processing_failed'));

        Storage::disk('public')->assertDirectoryEmpty('test-images');

        return;
    }

    test()->fail('Malformed image content did not fail validation.');
});
