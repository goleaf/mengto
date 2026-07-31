<?php

declare(strict_types=1);

use App\Http\Controllers\AnswerStoreController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BookingActionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingCreateController;
use App\Http\Controllers\BookingStoreController;
use App\Http\Controllers\CareAccessRevokeController;
use App\Http\Controllers\CareAccessStoreController;
use App\Http\Controllers\CareEntryStoreController;
use App\Http\Controllers\CareJournalController;
use App\Http\Controllers\CareJournalCreateController;
use App\Http\Controllers\CareJournalDirectoryController;
use App\Http\Controllers\CareJournalManageController;
use App\Http\Controllers\CareJournalReportController;
use App\Http\Controllers\CareJournalStoreController;
use App\Http\Controllers\CareMediaDownloadController;
use App\Http\Controllers\CareRoutineStoreController;
use App\Http\Controllers\CareSharedEntryStoreController;
use App\Http\Controllers\CareSharedJournalController;
use App\Http\Controllers\CareSharedMediaDownloadController;
use App\Http\Controllers\CareTaskCompleteController;
use App\Http\Controllers\CareTaskStoreController;
use App\Http\Controllers\CirclePreviewController;
use App\Http\Controllers\CommentStoreController;
use App\Http\Controllers\ComposerController;
use App\Http\Controllers\ConnectionCenterPreviewController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\ConversationDetailPreviewController;
use App\Http\Controllers\CorrectionStoreController;
use App\Http\Controllers\CreatedContentPreviewController;
use App\Http\Controllers\DeviceAccessRevokeController;
use App\Http\Controllers\DeviceAccessStoreController;
use App\Http\Controllers\DeviceAutomationStoreController;
use App\Http\Controllers\DeviceAutomationTestController;
use App\Http\Controllers\DeviceCommandStoreController;
use App\Http\Controllers\DeviceEventAcknowledgeController;
use App\Http\Controllers\DeviceEventCareEntryController;
use App\Http\Controllers\DeviceLifecycleStoreController;
use App\Http\Controllers\DeviceReadingMedicalEventController;
use App\Http\Controllers\DeviceReadingStoreController;
use App\Http\Controllers\DeviceRetentionUpdateController;
use App\Http\Controllers\DeviceSafeZoneStoreController;
use App\Http\Controllers\DeviceSharedDashboardController;
use App\Http\Controllers\DiscoverPreviewController;
use App\Http\Controllers\ExpertActionController;
use App\Http\Controllers\ExpertDashboardController;
use App\Http\Controllers\ExpertDirectoryController;
use App\Http\Controllers\ExpertProfileController;
use App\Http\Controllers\ExpertProfileCreateController;
use App\Http\Controllers\ExpertProfileEditController;
use App\Http\Controllers\ExpertProfileStoreController;
use App\Http\Controllers\ExpertProfileUpdateController;
use App\Http\Controllers\ForumActionController;
use App\Http\Controllers\ForumAdministrationController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumExpertSessionDirectoryController;
use App\Http\Controllers\ForumExpertSessionShowController;
use App\Http\Controllers\ForumGroupDirectoryController;
use App\Http\Controllers\ForumGroupFileDownloadController;
use App\Http\Controllers\ForumGroupShowController;
use App\Http\Controllers\ForumJournalDirectoryController;
use App\Http\Controllers\ForumJournalExportController;
use App\Http\Controllers\ForumJournalMediaController;
use App\Http\Controllers\ForumMentorshipController;
use App\Http\Controllers\GroupDetailPreviewController;
use App\Http\Controllers\GroupDirectoryPreviewController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\KnowledgeGuideCreateController;
use App\Http\Controllers\KnowledgeGuideEditController;
use App\Http\Controllers\KnowledgeGuideExportController;
use App\Http\Controllers\KnowledgeGuidePrintController;
use App\Http\Controllers\KnowledgeGuideTranslationCreateController;
use App\Http\Controllers\ListingActionController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingCreateController;
use App\Http\Controllers\ListingDirectoryController;
use App\Http\Controllers\ListingReviewController;
use App\Http\Controllers\ListingStoreController;
use App\Http\Controllers\MedicalAccessRevokeController;
use App\Http\Controllers\MedicalAccessStoreController;
use App\Http\Controllers\MedicalDocumentDownloadController;
use App\Http\Controllers\MedicalDocumentStoreController;
use App\Http\Controllers\MedicalEmergencyCardController;
use App\Http\Controllers\MedicalEntryStoreController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicalRecordCreateController;
use App\Http\Controllers\MedicalRecordDirectoryController;
use App\Http\Controllers\MedicalRecordManageController;
use App\Http\Controllers\MedicalRecordStoreController;
use App\Http\Controllers\MedicalSharedDocumentDownloadController;
use App\Http\Controllers\MedicalSharedRecordController;
use App\Http\Controllers\MedicationDoseStoreController;
use App\Http\Controllers\MeetupDetailPreviewController;
use App\Http\Controllers\MeetupDirectoryPreviewController;
use App\Http\Controllers\MemberProfilePreviewController;
use App\Http\Controllers\MessageCenterPreviewController;
use App\Http\Controllers\NeighborDirectoryPreviewController;
use App\Http\Controllers\NeighborProfilePreviewController;
use App\Http\Controllers\NotificationCenterPreviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDisputeController;
use App\Http\Controllers\PerformActionController;
use App\Http\Controllers\PerformMessageActionController;
use App\Http\Controllers\PetDirectoryPreviewController;
use App\Http\Controllers\PetFriendCenterPreviewController;
use App\Http\Controllers\PetProfilePreviewController;
use App\Http\Controllers\PhotoInteractionController;
use App\Http\Controllers\PlaceDetailPreviewController;
use App\Http\Controllers\PlaceDirectoryPreviewController;
use App\Http\Controllers\PostThreadPreviewController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\ReviewStoreController;
use App\Http\Controllers\SearchActionController;
use App\Http\Controllers\SearchCaseController;
use App\Http\Controllers\SearchCaseCreateController;
use App\Http\Controllers\SearchCaseStoreController;
use App\Http\Controllers\SearchContactRelayController;
use App\Http\Controllers\SearchCoordinationController;
use App\Http\Controllers\SearchDirectoryController;
use App\Http\Controllers\SearchPosterController;
use App\Http\Controllers\SearchReportController;
use App\Http\Controllers\SharePreviewController;
use App\Http\Controllers\SightingStoreController;
use App\Http\Controllers\SimilarTopicController;
use App\Http\Controllers\SmartDeviceController;
use App\Http\Controllers\SmartDeviceCreateController;
use App\Http\Controllers\SmartDeviceDirectoryController;
use App\Http\Controllers\SmartDeviceManageController;
use App\Http\Controllers\SmartDeviceStoreController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\TopicCreateController;
use App\Http\Controllers\TopicDeleteController;
use App\Http\Controllers\TopicEditController;
use App\Http\Controllers\TopicStoreController;
use App\Http\Controllers\TopicUpdateController;
use App\Http\Controllers\WalkPlanPreviewController;
use App\Http\Middleware\ProtectCareResponse;
use App\Http\Middleware\ProtectDeviceResponse;
use App\Http\Middleware\ProtectMedicalResponse;
use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Pets\CreatePetProfile;
use App\Livewire\Pets\ManagePetProfile;
use App\Livewire\Pets\PetProfileInvitations;
use App\Livewire\Pets\PublicPetProfile;
use App\Livewire\ProfileSettings;
use App\Livewire\Social\RelationshipCenter;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->group(function (): void {
        Route::get('/', PreviewController::class)->name('home');

        Route::middleware('guest')
            ->group(function (): void {
                Route::get('/login', Login::class)->name('login');
                Route::get('/register', Register::class)->name('register');
                Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
                Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
            });

        Route::middleware(['auth', 'active'])
            ->group(function (): void {
                Route::get('/confirm-password', ConfirmPassword::class)
                    ->name('password.confirm');
                Route::get('/verify-email', VerifyEmail::class)->name('verification.notice');
                Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                    ->middleware(['signed', 'throttle:6,1'])
                    ->name('verification.verify');
                Route::post('/logout', LogoutController::class)->name('logout');

                Route::prefix('profile')
                    ->name('profile.')
                    ->group(function (): void {
                        Route::get('/settings', ProfileSettings::class)->name('settings');
                    });

                Route::prefix('pets/manage')
                    ->name('pets.manage.')
                    ->group(function (): void {
                        Route::get('/new', CreatePetProfile::class)
                            ->middleware('throttle:30,1')
                            ->name('create');
                        Route::get('/invitations', PetProfileInvitations::class)
                            ->name('invitations');
                        Route::get('/{petProfile:profile_key}', ManagePetProfile::class)
                            ->name('show');
                    });

                Route::get('/circle', CirclePreviewController::class)->name('circle.index');
                Route::get('/circle/connections', ConnectionCenterPreviewController::class)
                    ->name('connections.index');
                Route::get('/circle/pet-friends', PetFriendCenterPreviewController::class)
                    ->name('pet-friends.index');
                Route::prefix('circle/social')
                    ->name('social.')
                    ->group(function (): void {
                        Route::get('/', RelationshipCenter::class)->name('index');
                    });
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
                Route::get('/notifications', NotificationCenterPreviewController::class)
                    ->name('notifications.index');
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
                Route::post('/photos/actions', PhotoInteractionController::class)
                    ->middleware('throttle:40,1')
                    ->name('photos.interactions.store');
            });

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
            ->where('event', '[A-Za-z0-9-]+')
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
        Route::prefix('forum')
            ->name('forum.')
            ->group(function (): void {
                Route::get('/', ForumController::class)->name('index');
                Route::get('/similar', SimilarTopicController::class)->name('topics.similar');
                Route::get('/expert-sessions', ForumExpertSessionDirectoryController::class)
                    ->name('expert-sessions.index');
                Route::get(
                    '/expert-sessions/{forumExpertSession:stable_key}',
                    ForumExpertSessionShowController::class,
                )->name('expert-sessions.show');
                Route::get('/topics/{forumTopic}', TopicController::class)->name('topics.show');
                Route::get(
                    '/journals/{forumJournal:stable_key}/media/{forumJournalMedia:stable_key}',
                    ForumJournalMediaController::class,
                )
                    ->withoutScopedBindings()
                    ->middleware('throttle:60,1')
                    ->name('journals.media.show');

                Route::middleware(['auth', 'active'])
                    ->group(function (): void {
                        Route::get('/ask', TopicCreateController::class)->name('topics.create');
                        Route::post('/topics', TopicStoreController::class)
                            ->middleware('throttle:12,1')
                            ->name('topics.store');
                        Route::get('/topics/{forumTopic}/edit', TopicEditController::class)
                            ->name('topics.edit');
                        Route::put('/topics/{forumTopic}', TopicUpdateController::class)
                            ->name('topics.update');
                        Route::delete('/topics/{forumTopic}', TopicDeleteController::class)
                            ->name('topics.destroy');
                        Route::post('/topics/{forumTopic}/answers', AnswerStoreController::class)
                            ->middleware('throttle:24,1')
                            ->name('answers.store');
                        Route::post('/topics/{forumTopic}/comments', CommentStoreController::class)
                            ->middleware('throttle:40,1')
                            ->name('comments.store');
                        Route::post('/actions', ForumActionController::class)
                            ->middleware('throttle:60,1')
                            ->name('actions');
                        Route::get('/mentorship', ForumMentorshipController::class)
                            ->middleware('verified')
                            ->name('mentorship.index');
                        Route::middleware('verified')
                            ->prefix('journals')
                            ->name('journals.')
                            ->group(function (): void {
                                Route::get('/', ForumJournalDirectoryController::class)
                                    ->name('index');
                                Route::get(
                                    '/{forumJournal:stable_key}/export',
                                    ForumJournalExportController::class,
                                )
                                    ->middleware('throttle:12,1')
                                    ->name('export');
                            });
                        Route::middleware('verified')
                            ->prefix('groups')
                            ->name('groups.')
                            ->group(function (): void {
                                Route::get('/', ForumGroupDirectoryController::class)
                                    ->name('index');
                                Route::get(
                                    '/{forumGroup:stable_key}/files/{file:stable_key}',
                                    ForumGroupFileDownloadController::class,
                                )
                                    ->middleware('throttle:60,1')
                                    ->name('files.download');
                                Route::get('/{forumGroup:stable_key}', ForumGroupShowController::class)
                                    ->name('show');
                            });
                    });
            });
        Route::prefix('knowledge')
            ->name('knowledge.')
            ->group(function (): void {
                Route::get('/', KnowledgeController::class)->name('index');
                Route::middleware(['auth', 'active', 'verified'])
                    ->group(function (): void {
                        Route::get('/guides/new', KnowledgeGuideCreateController::class)
                            ->name('guides.create');
                        Route::get(
                            '/{knowledgeArticle}/translations/new',
                            KnowledgeGuideTranslationCreateController::class,
                        )->name('guides.translations.create');
                        Route::get('/{knowledgeArticle}/edit', KnowledgeGuideEditController::class)
                            ->name('guides.edit');
                    });
                Route::get('/{knowledgeArticle}/export', KnowledgeGuideExportController::class)
                    ->name('articles.export');
                Route::get('/{knowledgeArticle}/print', KnowledgeGuidePrintController::class)
                    ->name('articles.print');
                Route::get('/{knowledgeArticle}', ArticleController::class)->name('articles.show');
                Route::post('/{knowledgeArticle}/corrections', CorrectionStoreController::class)
                    ->middleware(['auth', 'active', 'throttle:12,1'])
                    ->name('corrections.store');
            });
        Route::get('/neighbors', NeighborDirectoryPreviewController::class)->name('neighbors.index');
        Route::get('/neighbors/ari-jensen', NeighborProfilePreviewController::class)->name('neighbors.ari');
        Route::get('/pets', PetDirectoryPreviewController::class)->name('pets.index');
        Route::prefix('pets/profile')
            ->name('pets.')
            ->group(function (): void {
                Route::get('/{petProfile:profile_key}', PublicPetProfile::class)
                    ->name('profile');
            });
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
    });

Route::middleware('web')
    ->prefix('experts')
    ->name('experts.')
    ->group(function (): void {
        Route::get('/', ExpertDirectoryController::class)->name('index');
        Route::middleware(['auth', 'active', 'verified'])
            ->group(function (): void {
                Route::get('/new', ExpertProfileCreateController::class)->name('create');
                Route::post('/', ExpertProfileStoreController::class)
                    ->middleware('throttle:6,1')
                    ->name('store');
                Route::get('/workspace', ExpertDashboardController::class)->name('dashboard');
                Route::get('/{expertProfile}/edit', ExpertProfileEditController::class)->name('edit');
                Route::put('/{expertProfile}', ExpertProfileUpdateController::class)->name('update');
                Route::get('/{expertProfile}/book', BookingCreateController::class)
                    ->name('bookings.create');
                Route::post('/{expertProfile}/book', BookingStoreController::class)
                    ->middleware('throttle:8,1')
                    ->name('bookings.store');
                Route::post('/{expertProfile}/actions', ExpertActionController::class)
                    ->middleware('throttle:30,1')
                    ->name('actions');
                Route::post('/{expertProfile}/reviews', ReviewStoreController::class)
                    ->middleware('throttle:6,1')
                    ->name('reviews.store');
            });
        Route::get('/{expertProfile}', ExpertProfileController::class)->name('show');
    });

Route::middleware(['web', 'auth', 'active', 'verified'])
    ->prefix('bookings')
    ->name('bookings.')
    ->group(function (): void {
        Route::get('/{booking}', BookingController::class)->name('show');
        Route::post('/{booking}/actions', BookingActionController::class)
            ->middleware('throttle:20,1')
            ->name('actions');
    });

Route::middleware(['web', 'auth', 'active', 'verified'])
    ->prefix('admin/forum')
    ->name('admin.forum.')
    ->group(function (): void {
        Route::get('/', ForumAdministrationController::class)->name('index');
    });

Route::middleware(['web', 'auth', 'active', 'verified'])
    ->prefix('consultations')
    ->name('consultations.')
    ->group(function (): void {
        Route::get('/{consultation}', ConsultationController::class)->name('show');
    });

Route::middleware('web')
    ->prefix('marketplace')
    ->name('marketplace.')
    ->group(function (): void {
        Route::get('/', ListingDirectoryController::class)->name('index');
        Route::middleware(['auth', 'active', 'verified'])
            ->group(function (): void {
                Route::get('/new', ListingCreateController::class)->name('create');
                Route::post('/', ListingStoreController::class)
                    ->middleware('throttle:8,1')
                    ->name('store');
                Route::get('/{listing}/orders/{order}', OrderController::class)
                    ->scopeBindings()
                    ->name('orders.show');
                Route::post('/{listing}/orders/{order}/disputes', OrderDisputeController::class)
                    ->scopeBindings()
                    ->middleware('throttle:6,1')
                    ->name('orders.disputes.store');
                Route::post('/{listing}/orders/{order}/reviews', ListingReviewController::class)
                    ->scopeBindings()
                    ->middleware('throttle:6,1')
                    ->name('orders.reviews.store');
                Route::post('/{listing}/actions', ListingActionController::class)
                    ->middleware('throttle:30,1')
                    ->name('actions');
            });
        Route::get('/{listing}', ListingController::class)->name('show');
    });

Route::middleware('web')
    ->prefix('lost-found')
    ->name('lost-found.')
    ->group(function (): void {
        Route::get('/', SearchDirectoryController::class)->name('index');
        Route::middleware(['auth', 'active', 'verified'])
            ->group(function (): void {
                Route::get('/new', SearchCaseCreateController::class)->name('create');
                Route::post('/', SearchCaseStoreController::class)
                    ->middleware('throttle:6,1')
                    ->name('store');
                Route::get('/{searchCase}/coordinate', SearchCoordinationController::class)
                    ->name('coordinate');
                Route::post('/{searchCase}/actions', SearchActionController::class)
                    ->middleware('throttle:30,1')
                    ->name('actions');
                Route::post('/{searchCase}/contact', SearchContactRelayController::class)
                    ->middleware('throttle:6,1')
                    ->name('contact.store');
                Route::post('/{searchCase}/reports', SearchReportController::class)
                    ->middleware('throttle:6,1')
                    ->name('reports.store');
            });
        Route::get('/{searchCase}/poster', SearchPosterController::class)
            ->name('poster');
        Route::post('/{searchCase}/sightings', SightingStoreController::class)
            ->middleware('throttle:12,1')
            ->name('sightings.store');
        Route::get('/{searchCase}', SearchCaseController::class)->name('show');
    });

Route::middleware(['web', 'auth', 'active', 'verified', ProtectMedicalResponse::class])
    ->prefix('medical-records')
    ->name('medical-records.')
    ->group(function (): void {
        Route::get('/', MedicalRecordDirectoryController::class)->name('index');
        Route::get('/new', MedicalRecordCreateController::class)->name('create');
        Route::post('/', MedicalRecordStoreController::class)
            ->middleware('throttle:6,1')
            ->name('store');
        Route::get('/{medicalRecord}/manage', MedicalRecordManageController::class)
            ->name('manage');
        Route::get('/{medicalRecord}/emergency', MedicalEmergencyCardController::class)
            ->name('emergency');
        Route::post('/{medicalRecord}/entries', MedicalEntryStoreController::class)
            ->middleware('throttle:30,1')
            ->name('entries.store');
        Route::post('/{medicalRecord}/doses', MedicationDoseStoreController::class)
            ->middleware('throttle:40,1')
            ->name('doses.store');
        Route::post('/{medicalRecord}/documents', MedicalDocumentStoreController::class)
            ->middleware('throttle:12,1')
            ->name('documents.store');
        Route::get(
            '/{medicalRecord}/documents/{document}',
            MedicalDocumentDownloadController::class,
        )
            ->scopeBindings()
            ->name('documents.download');
        Route::post('/{medicalRecord}/access', MedicalAccessStoreController::class)
            ->middleware('throttle:12,1')
            ->name('access.store');
        Route::delete(
            '/{medicalRecord}/access/{medicalAccessGrant}',
            MedicalAccessRevokeController::class,
        )->name('access.revoke');
        Route::get('/{medicalRecord}', MedicalRecordController::class)->name('show');
    });

Route::middleware(['web', 'auth', 'active', 'verified', ProtectCareResponse::class])
    ->prefix('care-journals')
    ->name('care-journals.')
    ->group(function (): void {
        Route::get('/', CareJournalDirectoryController::class)->name('index');
        Route::get('/new', CareJournalCreateController::class)->name('create');
        Route::post('/', CareJournalStoreController::class)
            ->middleware('throttle:6,1')
            ->name('store');
        Route::get('/{careJournal}/manage', CareJournalManageController::class)
            ->name('manage');
        Route::get('/{careJournal}/report', CareJournalReportController::class)
            ->name('report');
        Route::post('/{careJournal}/entries', CareEntryStoreController::class)
            ->middleware('throttle:40,1')
            ->name('entries.store');
        Route::post('/{careJournal}/tasks', CareTaskStoreController::class)
            ->middleware('throttle:30,1')
            ->name('tasks.store');
        Route::post(
            '/{careJournal}/tasks/{careTask}/complete',
            CareTaskCompleteController::class,
        )
            ->middleware('throttle:40,1')
            ->name('tasks.complete');
        Route::post('/{careJournal}/routines', CareRoutineStoreController::class)
            ->middleware('throttle:20,1')
            ->name('routines.store');
        Route::post('/{careJournal}/access', CareAccessStoreController::class)
            ->middleware('throttle:12,1')
            ->name('access.store');
        Route::delete(
            '/{careJournal}/access/{careAccessGrant}',
            CareAccessRevokeController::class,
        )->name('access.revoke');
        Route::get(
            '/{careJournal}/media/{careMedia}',
            CareMediaDownloadController::class,
        )->name('media.download');
        Route::get('/{careJournal}', CareJournalController::class)->name('show');
    });

Route::middleware(['web', 'auth', 'active', 'verified', ProtectDeviceResponse::class])
    ->prefix('devices')
    ->name('devices.')
    ->group(function (): void {
        Route::get('/', SmartDeviceDirectoryController::class)->name('index');
        Route::get('/new', SmartDeviceCreateController::class)->name('create');
        Route::post('/', SmartDeviceStoreController::class)
            ->middleware('throttle:6,1')
            ->name('store');
        Route::get('/{smartDevice}/manage', SmartDeviceManageController::class)
            ->middleware('password.confirm')
            ->name('manage');
        Route::post('/{smartDevice}/readings', DeviceReadingStoreController::class)
            ->middleware('throttle:40,1')
            ->name('readings.store');
        Route::post('/{smartDevice}/commands', DeviceCommandStoreController::class)
            ->middleware(['password.confirm', 'throttle:20,1'])
            ->name('commands.store');
        Route::put('/{smartDevice}/retention', DeviceRetentionUpdateController::class)
            ->middleware(['password.confirm', 'throttle:12,1'])
            ->name('retention.update');
        Route::post('/{smartDevice}/lifecycle', DeviceLifecycleStoreController::class)
            ->middleware(['password.confirm', 'throttle:12,1'])
            ->name('lifecycle.store');
        Route::post(
            '/{smartDevice}/events/{deviceEvent}/acknowledge',
            DeviceEventAcknowledgeController::class,
        )->name('events.acknowledge');
        Route::post(
            '/{smartDevice}/events/{deviceEvent}/care-entry',
            DeviceEventCareEntryController::class,
        )->name('events.care-entry');
        Route::post(
            '/{smartDevice}/readings/{deviceReading}/medical-entry',
            DeviceReadingMedicalEventController::class,
        )->name('readings.medical-entry');
        Route::post('/{smartDevice}/safe-zones', DeviceSafeZoneStoreController::class)
            ->middleware('throttle:12,1')
            ->name('safe-zones.store');
        Route::post('/{smartDevice}/automations', DeviceAutomationStoreController::class)
            ->middleware('throttle:12,1')
            ->name('automations.store');
        Route::post(
            '/{smartDevice}/automations/{deviceAutomation}/test',
            DeviceAutomationTestController::class,
        )
            ->middleware('throttle:20,1')
            ->name('automations.test');
        Route::post('/{smartDevice}/access', DeviceAccessStoreController::class)
            ->middleware('throttle:12,1')
            ->name('access.store');
        Route::delete(
            '/{smartDevice}/access/{deviceAccessGrant}',
            DeviceAccessRevokeController::class,
        )->name('access.revoke');
        Route::get('/{smartDevice}', SmartDeviceController::class)
            ->middleware('password.confirm')
            ->name('show');
    });

Route::middleware(['web', ProtectCareResponse::class])
    ->prefix('care-access')
    ->name('care-access.')
    ->group(function (): void {
        Route::get('/{token}', CareSharedJournalController::class)
            ->where('token', '[A-Za-z0-9]{64}')
            ->middleware('throttle:30,1')
            ->name('show');
        Route::post('/{token}/entries', CareSharedEntryStoreController::class)
            ->where('token', '[A-Za-z0-9]{64}')
            ->middleware('throttle:20,1')
            ->name('entries.store');
        Route::get('/{token}/media/{careMedia}', CareSharedMediaDownloadController::class)
            ->where('token', '[A-Za-z0-9]{64}')
            ->middleware('throttle:20,1')
            ->name('media.download');
    });

Route::middleware(['web', ProtectMedicalResponse::class])
    ->prefix('medical-access')
    ->name('medical-access.')
    ->group(function (): void {
        Route::get('/{token}', MedicalSharedRecordController::class)
            ->where('token', '[A-Za-z0-9]{64}')
            ->middleware('throttle:30,1')
            ->name('show');
        Route::get(
            '/{token}/documents/{medicalDocument}',
            MedicalSharedDocumentDownloadController::class,
        )
            ->where('token', '[A-Za-z0-9]{64}')
            ->middleware('throttle:20,1')
            ->name('documents.download');
    });

Route::middleware(['web', ProtectDeviceResponse::class])
    ->prefix('device-access')
    ->name('device-access.')
    ->group(function (): void {
        Route::get('/{token}', DeviceSharedDashboardController::class)
            ->where('token', '[A-Za-z0-9]{64}')
            ->middleware('throttle:30,1')
            ->name('show');
    });
