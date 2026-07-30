<?php

use Illuminate\Support\Facades\Route;

test('the preview renders as a static app shell', function () {
    expect(Route::has('pet-social.preview'))->toBeTrue();

    $response = $this->get(route('pet-social.preview'));

    $response
        ->assertSuccessful()
        ->assertSee('PawCircle')
        ->assertSee('data-section="feed"', false)
        ->assertSee('data-section="pets"', false)
        ->assertSee('data-section="meetups"', false)
        ->assertSee('data-section="groups"', false)
        ->assertSee('data-section="tips"', false)
        ->assertSee('aria-disabled="true"', false)
        ->assertDontSee('<form', false)
        ->assertDontSee('Laravel');
});
