<?php

declare(strict_types=1);

use App\Http\Controllers\ForumGroupDirectoryController;
use App\Http\Controllers\ForumGroupShowController;
use App\Models\ForumGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('the primary group routes use the canonical persisted group domain', function (): void {
    $directory = Route::getRoutes()->getByName('groups.index');
    $detail = Route::getRoutes()->getByName('groups.show');

    expect($directory)
        ->not->toBeNull()
        ->and($directory?->uri())->toBe('groups')
        ->and($directory?->getActionName())->toBe(ForumGroupDirectoryController::class)
        ->and($directory?->gatherMiddleware())->toContain('auth', 'active', 'verified')
        ->and($detail)
        ->not->toBeNull()
        ->and($detail?->uri())->toBe('groups/{forumGroup}')
        ->and($detail?->getActionName())->toBe(ForumGroupShowController::class);
});

test('the group directory renders arbitrary persisted groups and an honest empty state', function (): void {
    $viewer = User::factory()->create();
    $this->actingAs($viewer);

    $this->get(route('groups.index'))
        ->assertOk()
        ->assertSee(__('forum_groups.empty.groups_title'))
        ->assertDontSee('Apartment Pets PDX');

    $group = ForumGroup::factory()->create([
        'name' => 'Elina test community',
        'description' => 'A persisted community created only for this request.',
    ]);

    $this->get(route('groups.index'))
        ->assertOk()
        ->assertSee('Elina test community')
        ->assertSee(route('groups.show', $group), false);

    $this->get(route('groups.show', $group))
        ->assertOk()
        ->assertSee('Elina test community');
});

test('renaming a persisted group propagates without a route or presenter constant', function (): void {
    $viewer = User::factory()->create();
    $group = ForumGroup::factory()->create(['name' => 'Original data name']);
    $this->actingAs($viewer);

    $this->get(route('groups.show', $group))->assertSee('Original data name');

    $group->forceFill(['name' => 'Renamed from persistence'])->saveOrFail();

    $this->get(route('groups.show', $group))
        ->assertOk()
        ->assertSee('Renamed from persistence')
        ->assertDontSee('Original data name');
});
