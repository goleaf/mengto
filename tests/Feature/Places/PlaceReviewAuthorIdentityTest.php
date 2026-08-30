<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\PlaceState;
use Database\Seeders\PlaceDemoSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(PlaceDemoSeeder::class);
});

test('place reviews derive unicode author identity from the authenticated account', function (): void {
    $user = User::factory()->create([
        'actor_key' => 'zivile-petraite',
        'name' => 'Živilė Petraitė',
        'email' => 'zivile@example.com',
        'email_verified_at' => now(),
        'status' => UserStatus::Active,
    ]);
    actingAs($user);

    $response = post(route('actions.perform'), [
        'action' => 'create-place-review',
        'target' => 'vingis-quiet-loop',
        'place_rating' => 5,
        'place_pet' => 'scout',
        'place_review_criterion' => 'safety',
        'place_anonymous' => 'no',
        'body' => 'The quiet loop was clearly marked.',
        'author' => 'Forged Browser Name',
        'initials' => 'FB',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $review = app(PlaceState::class)->reviews('vingis-quiet-loop')[0];

    expect($review['author'])->toBe('Živilė Petraitė')
        ->and($review['initials'])->toBe('ŽP')
        ->and($review)->not->toContain('Forged Browser Name', 'FB');
});
