<?php

use App\Http\Controllers\CirclePreviewController;
use App\Http\Controllers\ConnectionCenterPreviewController;
use App\Http\Controllers\ConversationDetailPreviewController;
use App\Http\Controllers\CreatedContentPreviewController;
use App\Http\Controllers\DiscoverPreviewController;
use App\Http\Controllers\GroupDetailPreviewController;
use App\Http\Controllers\GroupDirectoryPreviewController;
use App\Http\Controllers\MeetupDetailPreviewController;
use App\Http\Controllers\MeetupDirectoryPreviewController;
use App\Http\Controllers\MemberProfilePreviewController;
use App\Http\Controllers\MessageCenterPreviewController;
use App\Http\Controllers\NeighborDirectoryPreviewController;
use App\Http\Controllers\NeighborProfilePreviewController;
use App\Http\Controllers\NotificationCenterPreviewController;
use App\Http\Controllers\ComposerController;
use App\Http\Controllers\PerformActionController;
use App\Http\Controllers\PerformMessageActionController;
use App\Http\Controllers\PetDirectoryPreviewController;
use App\Http\Controllers\PetFriendCenterPreviewController;
use App\Http\Controllers\PetProfilePreviewController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\PlaceDetailPreviewController;
use App\Http\Controllers\PlaceDirectoryPreviewController;
use App\Http\Controllers\PostThreadPreviewController;
use App\Http\Controllers\SharePreviewController;
use App\Http\Controllers\WalkPlanPreviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix('')
    ->name('pet-social.')
    ->group(function (): void {
        Route::get('/', PreviewController::class)->name('preview');
        Route::get('/circle', CirclePreviewController::class)->name('circle.index');
        Route::get('/circle/connections', ConnectionCenterPreviewController::class)->name('connections.index');
        Route::get('/circle/pet-friends', PetFriendCenterPreviewController::class)->name('pet-friends.index');
        Route::get('/discover', DiscoverPreviewController::class)->name('discover.index');
        Route::get('/groups', GroupDirectoryPreviewController::class)->name('groups.index');
        Route::get('/groups/apartment-pets-pdx', GroupDetailPreviewController::class)
            ->defaults('group', 'apartment-pets')
            ->name('groups.apartment_pets');
        Route::get('/groups/{item}', CreatedContentPreviewController::class)
            ->defaults('kind', 'group')
            ->where('item', 'created-group-[A-Za-z0-9-]+')
            ->name('groups.created');
        Route::get('/groups/{group}', GroupDetailPreviewController::class)
            ->whereIn('group', [
                'apartment-pets',
                'trail-tails',
                'cat-people',
                'foster-network',
                'portland-labradors',
                'senior-companions',
            ])
            ->name('groups.show');
        Route::get('/meetups', MeetupDirectoryPreviewController::class)->name('meetups.index');
        Route::get('/meetups/small-dog-social', MeetupDetailPreviewController::class)
            ->defaults('event', 'small-dog-social')
            ->name('meetups.small_dog_social');
        Route::get('/meetups/{item}', CreatedContentPreviewController::class)
            ->defaults('kind', 'meetup')
            ->where('item', 'created-meetup-[A-Za-z0-9-]+')
            ->name('meetups.created');
        Route::get('/meetups/{event}', MeetupDetailPreviewController::class)
            ->whereIn('event', [
                'puppy-social-lab',
                'beginner-training-series',
                'rose-city-pet-show',
                'shelter-open-house',
                'missing-scout-search',
                'baxter-birthday',
                'travel-ready-webinar',
            ])
            ->name('meetups.show');
        Route::get('/places', PlaceDirectoryPreviewController::class)->name('places.index');
        Route::get('/places/{place}', PlaceDetailPreviewController::class)
            ->whereIn('place', [
                'vingis-quiet-loop',
                'bernardine-evening-park',
                'pavilniai-calm-trail',
                'zverynas-small-dog-run',
                'naujininkai-secure-dog-field',
                'paws-24-veterinary-center',
                'night-paw-clinic',
                'green-paw-neighborhood-clinic',
                'quiet-whiskers-grooming',
                'old-town-pet-cafe',
                'city-pet-market',
                'vilnius-animal-aid',
            ])
            ->name('places.show');
        Route::get('/messages', MessageCenterPreviewController::class)->name('messages.index');
        Route::get('/messages/{conversation}/details', ConversationDetailPreviewController::class)
            ->whereIn('conversation', [
                'ari',
                'family-care',
                'vingis-walk',
                'paws-vet',
                'foster-adoption',
                'lost-luna',
                'trail-tails',
                'luna-request',
            ])
            ->name('messages.details');
        Route::post('/messages/actions', PerformMessageActionController::class)
            ->name('messages.actions');
        Route::get('/walks', WalkPlanPreviewController::class)->name('walks.index');
        Route::get('/neighbors', NeighborDirectoryPreviewController::class)->name('neighbors.index');
        Route::get('/neighbors/ari-jensen', NeighborProfilePreviewController::class)->name('neighbors.ari');
        Route::get('/notifications', NotificationCenterPreviewController::class)->name('notifications.index');
        Route::get('/pets', PetDirectoryPreviewController::class)->name('pets.index');
        Route::get('/@mia-carter/scout', PetProfilePreviewController::class)
            ->defaults('pet', 'scout')
            ->name('pets.scout');
        Route::get('/@mia-carter/nori', PetProfilePreviewController::class)
            ->defaults('pet', 'nori')
            ->name('pets.nori');
        Route::redirect('/pets/scout', '/@mia-carter/scout', 301)->name('pets.scout.legacy');
        Route::get('/pets/{item}', CreatedContentPreviewController::class)
            ->defaults('kind', 'pet')
            ->where('item', 'created-pet-[A-Za-z0-9-]+')
            ->name('pets.created');
        Route::get('/posts/{post}', PostThreadPreviewController::class)
            ->where('post', '[A-Za-z0-9-]+')
            ->name('posts.show');
        Route::get('/@mia-carter', MemberProfilePreviewController::class)->name('profile.mia');
        Route::redirect('/profile/mia-carter', '/@mia-carter', 301)->name('profile.mia.legacy');
        Route::get('/share/{target}', SharePreviewController::class)
            ->where('target', '[A-Za-z0-9-]+')
            ->name('share.show');
        Route::get('/compose/{kind}', ComposerController::class)
            ->whereIn('kind', [
                'post',
                'group',
                'meetup',
                'walk',
                'pet',
                'place',
                'place-correction',
                'place-warning',
                'place-review',
                'place-question',
                'place-claim',
                'message',
                'profile',
                'pet-profile',
                'profile-privacy',
                'pet-privacy',
                'report-profile',
                'post-edit',
                'report-post',
                'report-group',
                'report-event',
                'report-place',
                'delete-post',
            ])
            ->name('compose');
        Route::post('/actions', PerformActionController::class)->name('actions.perform');
    });
