<?php

declare(strict_types=1);

use App\Http\Middleware\RequirePortalAccess;
use App\Models\User;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

test('only account entry pages remain available to guests', function (string $routeName, array $parameters = []) {
    auth()->logout();

    $this->get(route($routeName, $parameters))->assertSuccessful();
})->with([
    'login' => ['login'],
    'register' => ['register'],
    'forgot password' => ['password.request'],
    'reset password' => ['password.reset', ['token' => 'guest-reset-token']],
]);

test('guest product pages redirect before route model binding', function (string $routeName, array $parameters = []) {
    auth()->logout();
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route($routeName, $parameters))
        ->assertRedirect(route('login'));

    expect(DB::getQueryLog())->toBeEmpty();
})->with([
    'home' => ['home'],
    'content directory' => ['content.index'],
    'missing content detail' => ['content.show', ['contentPublication' => 'missing-publication']],
    'discover' => ['discover.index'],
    'groups' => ['groups.index'],
    'meetups' => ['meetups.index'],
    'places' => ['places.index'],
    'forum' => ['forum.index'],
    'missing forum topic' => ['forum.topics.show', ['forumTopic' => 'missing-topic']],
    'knowledge' => ['knowledge.index'],
    'missing knowledge article' => ['knowledge.articles.show', ['knowledgeArticle' => 999999]],
    'experts' => ['experts.index'],
    'missing expert' => ['experts.show', ['expertProfile' => 999999]],
    'marketplace' => ['marketplace.index'],
    'missing listing' => ['marketplace.show', ['listing' => 999999]],
    'lost and found' => ['lost-found.index'],
    'missing search case' => ['lost-found.show', ['searchCase' => 999999]],
    'pets' => ['pets.index'],
    'missing pet profile' => ['pets.profile', ['petProfile' => 'missing-profile']],
    'neighbors' => ['neighbors.index'],
    'post' => ['posts.show', ['post' => 'missing-post']],
    'share' => ['share.show', ['target' => 'missing-share']],
    'care share' => ['care-access.show', ['token' => str_repeat('a', 64)]],
    'medical share' => ['medical-access.show', ['token' => str_repeat('b', 64)]],
    'device share' => ['device-access.show', ['token' => str_repeat('c', 64)]],
    'legacy pet redirect' => ['pets.scout.legacy'],
    'livewire file preview' => ['livewire.preview-file', ['filename' => 'missing-preview']],
]);

test('guest product mutations stop before persistence', function (string $method, string $routeName, array $parameters = []) {
    auth()->logout();
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->call($method, route($routeName, $parameters), [])
        ->assertRedirect(route('login'));

    expect(DB::getQueryLog())->toBeEmpty();
})->with([
    'forum action' => ['POST', 'forum.actions'],
    'lost found sighting' => ['POST', 'lost-found.sightings.store', ['searchCase' => 999999]],
    'care share entry' => ['POST', 'care-access.entries.store', ['token' => str_repeat('d', 64)]],
    'legacy pet mutation redirect' => ['DELETE', 'pets.scout.legacy'],
    'livewire file upload' => ['POST', 'livewire.upload-file'],
]);

test('guest json requests receive an unauthenticated response without product data', function () {
    auth()->logout();

    $this->getJson(route('content.index'))
        ->assertUnauthorized()
        ->assertJsonMissingPath('data');
});

test('unverified accounts can verify but cannot enter product pages', function () {
    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified);

    $this->get(route('verification.notice'))->assertSuccessful();
    $this->get(route('content.index'))->assertRedirect(route('verification.notice'));
});

test('verified active accounts retain portal access', function () {
    $this->get(route('content.index'))->assertSuccessful();
});

test('portal access runs before route binding and direct local storage serving is disabled', function () {
    $route = Route::getRoutes()->getByName('content.show');
    $middleware = app('router')->gatherRouteMiddleware($route);
    $portalPosition = array_search(RequirePortalAccess::class, $middleware, true);
    $bindingPosition = array_search(SubstituteBindings::class, $middleware, true);

    expect($portalPosition)->toBeInt()
        ->and($bindingPosition)->toBeInt()
        ->and($portalPosition)->toBeLessThan($bindingPosition)
        ->and(Route::getRoutes()->getByName('storage.local'))->toBeNull()
        ->and(Route::getRoutes()->getByName('storage.local.upload'))->toBeNull();
});

test('every web route inherits the central portal access boundary', function (): void {
    collect(Route::getRoutes())
        ->filter(fn (Illuminate\Routing\Route $route): bool => in_array('web', $route->gatherMiddleware(), true))
        ->each(function (Illuminate\Routing\Route $route): void {
            expect(
                app('router')->gatherRouteMiddleware($route),
                $route->getName() ?? $route->uri(),
            )->toContain(RequirePortalAccess::class);
        });

    expect(Livewire::getPersistentMiddleware())
        ->toContain(RequirePortalAccess::class);
});

test('local boost browser log mutation is protected when the package registers it', function (): void {
    Route::post('/_test/boost-browser-logs', fn () => response()->json(['status' => 'logged']))
        ->name('boost.browser-logs');
    Route::getRoutes()->refreshNameLookups();
    auth()->logout();

    $this->post(route('boost.browser-logs'))
        ->assertRedirect(route('login'));
});
