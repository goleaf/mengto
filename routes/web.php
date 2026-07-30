<?php

use App\Http\Controllers\AnswerStoreController;
use App\Http\Controllers\ArticleController;
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
use App\Http\Controllers\ForumController;
use App\Http\Controllers\GroupDetailPreviewController;
use App\Http\Controllers\GroupDirectoryPreviewController;
use App\Http\Controllers\KnowledgeController;
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
use App\Http\Controllers\PlaceDetailPreviewController;
use App\Http\Controllers\PlaceDirectoryPreviewController;
use App\Http\Controllers\PostThreadPreviewController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\ReviewStoreController;
use App\Http\Controllers\SearchActionController;
use App\Http\Controllers\SearchCaseController;
use App\Http\Controllers\SearchCaseCreateController;
use App\Http\Controllers\SearchCaseStoreController;
use App\Http\Controllers\SearchCoordinationController;
use App\Http\Controllers\SearchDirectoryController;
use App\Http\Controllers\SearchPosterController;
use App\Http\Controllers\SearchReportController;
use App\Http\Controllers\SharePreviewController;
use App\Http\Controllers\SightingStoreController;
use App\Http\Controllers\SimilarTopicController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\TopicCreateController;
use App\Http\Controllers\TopicDeleteController;
use App\Http\Controllers\TopicEditController;
use App\Http\Controllers\TopicStoreController;
use App\Http\Controllers\TopicUpdateController;
use App\Http\Controllers\WalkPlanPreviewController;
use App\Http\Middleware\ProtectCareResponse;
use App\Http\Middleware\ProtectMedicalResponse;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->group(function (): void {
        Route::get('/', PreviewController::class)->name('home');
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
        Route::prefix('forum')
            ->name('forum.')
            ->group(function (): void {
                Route::get('/', ForumController::class)->name('index');
                Route::get('/ask', TopicCreateController::class)->name('topics.create');
                Route::get('/similar', SimilarTopicController::class)->name('topics.similar');
                Route::post('/topics', TopicStoreController::class)
                    ->middleware('throttle:12,1')
                    ->name('topics.store');
                Route::get('/topics/{forumTopic}', TopicController::class)->name('topics.show');
                Route::get('/topics/{forumTopic}/edit', TopicEditController::class)->name('topics.edit');
                Route::put('/topics/{forumTopic}', TopicUpdateController::class)->name('topics.update');
                Route::delete('/topics/{forumTopic}', TopicDeleteController::class)->name('topics.destroy');
                Route::post('/topics/{forumTopic}/answers', AnswerStoreController::class)
                    ->middleware('throttle:24,1')
                    ->name('answers.store');
                Route::post('/topics/{forumTopic}/comments', CommentStoreController::class)
                    ->middleware('throttle:40,1')
                    ->name('comments.store');
                Route::post('/actions', ForumActionController::class)
                    ->middleware('throttle:60,1')
                    ->name('actions');
            });
        Route::prefix('knowledge')
            ->name('knowledge.')
            ->group(function (): void {
                Route::get('/', KnowledgeController::class)->name('index');
                Route::get('/{knowledgeArticle}', ArticleController::class)->name('articles.show');
                Route::post('/{knowledgeArticle}/corrections', CorrectionStoreController::class)
                    ->middleware('throttle:12,1')
                    ->name('corrections.store');
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

Route::middleware('web')
    ->prefix('experts')
    ->name('experts.')
    ->group(function (): void {
        Route::get('/', ExpertDirectoryController::class)->name('index');
        Route::get('/new', ExpertProfileCreateController::class)->name('create');
        Route::post('/', ExpertProfileStoreController::class)
            ->middleware('throttle:6,1')
            ->name('store');
        Route::get('/workspace', ExpertDashboardController::class)->name('dashboard');
        Route::get('/{expertProfile}/edit', ExpertProfileEditController::class)->name('edit');
        Route::put('/{expertProfile}', ExpertProfileUpdateController::class)->name('update');
        Route::get('/{expertProfile}/book', BookingCreateController::class)->name('bookings.create');
        Route::post('/{expertProfile}/book', BookingStoreController::class)
            ->middleware('throttle:8,1')
            ->name('bookings.store');
        Route::post('/{expertProfile}/actions', ExpertActionController::class)
            ->middleware('throttle:30,1')
            ->name('actions');
        Route::post('/{expertProfile}/reviews', ReviewStoreController::class)
            ->middleware('throttle:6,1')
            ->name('reviews.store');
        Route::get('/{expertProfile}', ExpertProfileController::class)->name('show');
    });

Route::middleware('web')
    ->prefix('bookings')
    ->name('bookings.')
    ->group(function (): void {
        Route::get('/{booking}', BookingController::class)->name('show');
        Route::post('/{booking}/actions', BookingActionController::class)
            ->middleware('throttle:20,1')
            ->name('actions');
    });

Route::middleware('web')
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
        Route::get('/{listing}', ListingController::class)->name('show');
        Route::post('/{listing}/actions', ListingActionController::class)
            ->middleware('throttle:30,1')
            ->name('actions');
    });

Route::middleware('web')
    ->prefix('lost-found')
    ->name('lost-found.')
    ->group(function (): void {
        Route::get('/', SearchDirectoryController::class)->name('index');
        Route::get('/new', SearchCaseCreateController::class)->name('create');
        Route::post('/', SearchCaseStoreController::class)
            ->middleware('throttle:6,1')
            ->name('store');
        Route::get('/{searchCase}/coordinate', SearchCoordinationController::class)
            ->name('coordinate');
        Route::get('/{searchCase}/poster', SearchPosterController::class)
            ->name('poster');
        Route::post('/{searchCase}/sightings', SightingStoreController::class)
            ->middleware('throttle:12,1')
            ->name('sightings.store');
        Route::post('/{searchCase}/actions', SearchActionController::class)
            ->middleware('throttle:30,1')
            ->name('actions');
        Route::post('/{searchCase}/reports', SearchReportController::class)
            ->middleware('throttle:6,1')
            ->name('reports.store');
        Route::get('/{searchCase}', SearchCaseController::class)->name('show');
    });

Route::middleware(['web', ProtectMedicalResponse::class])
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
            '/{medicalRecord}/documents/{medicalDocument}',
            MedicalDocumentDownloadController::class,
        )->name('documents.download');
        Route::post('/{medicalRecord}/access', MedicalAccessStoreController::class)
            ->middleware('throttle:12,1')
            ->name('access.store');
        Route::delete(
            '/{medicalRecord}/access/{medicalAccessGrant}',
            MedicalAccessRevokeController::class,
        )->name('access.revoke');
        Route::get('/{medicalRecord}', MedicalRecordController::class)->name('show');
    });

Route::middleware(['web', ProtectCareResponse::class])
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
