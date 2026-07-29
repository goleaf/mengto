<?php

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
use App\Http\Controllers\PetDirectoryPreviewController;
use App\Http\Controllers\PetProfilePreviewController;
use App\Http\Controllers\PetSocialPreviewController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::withoutMiddleware([
    StartSession::class,
    ShareErrorsFromSession::class,
    PreventRequestForgery::class,
])
    ->middleware('web')
    ->prefix('')
    ->name('pet-social.')
    ->group(function (): void {
        Route::get('/', PetSocialPreviewController::class)->name('preview');
        Route::get('/discover', DiscoverPreviewController::class)->name('discover.index');
        Route::get('/groups', GroupDirectoryPreviewController::class)->name('groups.index');
        Route::get('/groups/apartment-pets-pdx', GroupDetailPreviewController::class)->name('groups.apartment_pets');
        Route::get('/meetups', MeetupDirectoryPreviewController::class)->name('meetups.index');
        Route::get('/meetups/small-dog-social', MeetupDetailPreviewController::class)->name('meetups.small_dog_social');
        Route::get('/messages', MessageCenterPreviewController::class)->name('messages.index');
        Route::get('/neighbors', NeighborDirectoryPreviewController::class)->name('neighbors.index');
        Route::get('/neighbors/ari-jensen', NeighborProfilePreviewController::class)->name('neighbors.ari');
        Route::get('/notifications', NotificationCenterPreviewController::class)->name('notifications.index');
        Route::get('/pets', PetDirectoryPreviewController::class)->name('pets.index');
        Route::get('/pets/scout', PetProfilePreviewController::class)->name('pets.scout');
        Route::get('/profile/mia-carter', MemberProfilePreviewController::class)->name('profile.mia');
    });
