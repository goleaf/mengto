# Portal Route Matrix

The executable source of truth is `routes/web.php`; `php artisan route:list
--json` reported 176 active routes on 2026-08-03. The canonical
`php artisan route:list --except-vendor --json` audit reported 165 first-party
routes and excluded 11 package/runtime endpoints.

## Event Routes

The four meetup routes remain active and appear once in the global matrix
below. None was removed or redirected by page-identity work. Product access is
guarded by the route middleware and resource policy; compatibility flows keep
their canonical event targets. Behaviour coverage remains mapped in
`tests/Support/route-coverage.php`.

## Global Page Identity Classification

The 111 first-party routes accepting `GET` are classified below. The executable
one-route/one-class ledger is
`tests/Support/page-identity-route-classification.php`; the route inventory test
fails when a route is added, removed, duplicated, or left unclassified.

| Class | Page identity decision | Primary verification |
| --- | --- | --- |
| `canonical-page` | Retain the shared `x-page-header` contract | `PageIdentityStandardizationTest` plus module tests |
| `migration-candidate` | Migrate the general page introduction to `x-page-header` | Package-specific red route and browser checks |
| `deliberate-detail-or-profile` | Retain only a token-compatible identity hero after the route audit | Module authorization tests plus representative browser checks |
| `authentication-shell` | Keep the isolated account-entry hierarchy | `AuthenticationTest` and account-entry browser checks |
| `special-document-or-scoped-access` | Keep its print, emergency, or token-scoped semantic document | Domain privacy and authorization tests |
| `file-response` | Do not add page identity to a streamed/download response | Domain download and containment tests |
| `structured-response` | Do not add page identity to JSON or another structured response | API/resource feature tests |
| `redirect` | Do not add page identity to a redirect endpoint | Route and access-boundary tests |

The runtime owner is the controller or class-based Livewire component. It owns
the prepared view data and remains the lookup point for the current template;
the decision column is the desired page-identity boundary.

| Name | URI | Runtime owner | Class | Decision |
| --- | --- | --- | --- | --- |
| `home` | `/` | `HomeController` | `redirect` | no page identity |
| `profile.mia` | `/@mia-carter` | `MemberProfilePreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `pets.nori` | `/@mia-carter/nori` | `PetProfilePreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `pets.scout` | `/@mia-carter/scout` | `PetProfilePreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `admin.forum.index` | `/admin/forum` | `ForumAdministrationController` | `canonical-page` | retain `x-page-header` |
| `bookings.show` | `/bookings/{booking}` | `BookingController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `care-access.show` | `/care-access/{token}` | `CareSharedJournalController` | `special-document-or-scoped-access` | retain isolated semantic document |
| `care-access.media.download` | `/care-access/{token}/media/{careMedia}` | `CareSharedMediaDownloadController` | `file-response` | no page identity |
| `care-journals.index` | `/care-journals` | `CareJournalDirectoryController` | `canonical-page` | retain `x-page-header` |
| `care-journals.create` | `/care-journals/new` | `CareJournalCreateController` | `canonical-page` | retain `x-page-header` |
| `care-journals.show` | `/care-journals/{careJournal}` | `CareJournalController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `care-journals.manage` | `/care-journals/{careJournal}/manage` | `CareJournalManageController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `care-journals.media.download` | `/care-journals/{careJournal}/media/{careMedia}` | `CareMediaDownloadController` | `file-response` | no page identity |
| `care-journals.report` | `/care-journals/{careJournal}/report` | `CareJournalReportController` | `special-document-or-scoped-access` | retain isolated semantic document |
| `circle.index` | `/circle` | `CirclePreviewController` | `canonical-page` | retain `x-page-header` |
| `connections.index` | `/circle/connections` | `ConnectionCenterPreviewController` | `canonical-page` | retain `x-page-header` |
| `pet-friends.index` | `/circle/pet-friends` | `PetFriendCenterPreviewController` | `canonical-page` | retain `x-page-header` |
| `social.index` | `/circle/social` | `Social\RelationshipCenter` | `canonical-page` | retain `x-page-header` |
| `compose` | `/compose/{kind}` | `ComposerController` | `canonical-page` | retain `x-page-header` |
| `password.confirm` | `/confirm-password` | `Auth\ConfirmPassword` | `authentication-shell` | retain auth shell |
| `consultations.show` | `/consultations/{consultation}` | `ConsultationController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `content.index` | `/content` | `ContentFeedController` | `canonical-page` | retain `x-page-header` |
| `content.show` | `/content/{contentPublication:publication_key}` | `ContentPublicationController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `device-access.show` | `/device-access/{token}` | `DeviceSharedDashboardController` | `special-document-or-scoped-access` | retain isolated semantic document |
| `devices.index` | `/devices` | `SmartDeviceDirectoryController` | `canonical-page` | retain `x-page-header` |
| `devices.create` | `/devices/new` | `SmartDeviceCreateController` | `canonical-page` | retain `x-page-header` |
| `devices.show` | `/devices/{smartDevice}` | `SmartDeviceController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `devices.manage` | `/devices/{smartDevice}/manage` | `SmartDeviceManageController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `discover.index` | `/discover` | `DiscoverPreviewController` | `canonical-page` | retain `x-page-header` |
| `experts.index` | `/experts` | `ExpertDirectoryController` | `canonical-page` | retain `x-page-header` |
| `experts.create` | `/experts/new` | `ExpertProfileCreateController` | `canonical-page` | retain `x-page-header` |
| `experts.dashboard` | `/experts/workspace` | `ExpertDashboardController` | `canonical-page` | retain `x-page-header` |
| `experts.show` | `/experts/{expertProfile}` | `ExpertProfileController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `experts.bookings.create` | `/experts/{expertProfile}/book` | `BookingCreateController` | `canonical-page` | retain `x-page-header` |
| `experts.edit` | `/experts/{expertProfile}/edit` | `ExpertProfileEditController` | `canonical-page` | retain `x-page-header` |
| `password.request` | `/forgot-password` | `Auth\ForgotPassword` | `authentication-shell` | retain auth shell |
| `forum.index` | `/forum` | `ForumController` | `canonical-page` | retain `x-page-header` |
| `forum.topics.create` | `/forum/ask` | `TopicCreateController` | `canonical-page` | retain `x-page-header` |
| `forum.expert-sessions.index` | `/forum/expert-sessions` | `ForumExpertSessionDirectoryController` | `canonical-page` | retain `x-page-header` |
| `forum.expert-sessions.show` | `/forum/expert-sessions/{forumExpertSession:stable_key}` | `ForumExpertSessionShowController` | `canonical-page` | retain `x-page-header` |
| `forum.groups.index` | `/forum/groups` | `ForumGroupDirectoryController` | `canonical-page` | retain `x-page-header` |
| `forum.groups.show` | `/forum/groups/{forumGroup:stable_key}` | `ForumGroupShowController` | `canonical-page` | retain `x-page-header` |
| `forum.groups.files.download` | `/forum/groups/{forumGroup:stable_key}/files/{file:stable_key}` | `ForumGroupFileDownloadController` | `file-response` | no page identity |
| `forum.journals.index` | `/forum/journals` | `ForumJournalDirectoryController` | `canonical-page` | retain `x-page-header` |
| `forum.journals.export` | `/forum/journals/{forumJournal:stable_key}/export` | `ForumJournalExportController` | `file-response` | no page identity |
| `forum.journals.media.show` | `/forum/journals/{forumJournal:stable_key}/media/{forumJournalMedia:stable_key}` | `ForumJournalMediaController` | `file-response` | no page identity |
| `forum.mentorship.index` | `/forum/mentorship` | `ForumMentorshipController` | `canonical-page` | retain `x-page-header` |
| `forum.topics.similar` | `/forum/similar` | `SimilarTopicController` | `structured-response` | no page identity |
| `forum.topics.show` | `/forum/topics/{forumTopic}` | `TopicController` | `canonical-page` | retain `x-page-header` |
| `forum.topics.edit` | `/forum/topics/{forumTopic}/edit` | `TopicEditController` | `canonical-page` | retain `x-page-header` |
| `groups.index` | `/groups` | `GroupDirectoryPreviewController` | `canonical-page` | retain `x-page-header` |
| `groups.apartment_pets` | `/groups/apartment-pets-pdx` | `GroupDetailPreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `groups.show` | `/groups/{group}` | `GroupDetailPreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `groups.created` | `/groups/{item}` | `CreatedContentPreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `knowledge.index` | `/knowledge` | `KnowledgeController` | `canonical-page` | retain `x-page-header` |
| `knowledge.guides.create` | `/knowledge/guides/new` | `KnowledgeGuideCreateController` | `canonical-page` | retain `x-page-header` |
| `knowledge.articles.show` | `/knowledge/{knowledgeArticle}` | `ArticleController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `knowledge.guides.edit` | `/knowledge/{knowledgeArticle}/edit` | `KnowledgeGuideEditController` | `canonical-page` | retain `x-page-header` |
| `knowledge.articles.export` | `/knowledge/{knowledgeArticle}/export` | `KnowledgeGuideExportController` | `file-response` | no page identity |
| `knowledge.articles.print` | `/knowledge/{knowledgeArticle}/print` | `KnowledgeGuidePrintController` | `special-document-or-scoped-access` | retain isolated semantic document |
| `knowledge.guides.translations.create` | `/knowledge/{knowledgeArticle}/translations/new` | `KnowledgeGuideTranslationCreateController` | `canonical-page` | retain `x-page-header` |
| `login` | `/login` | `Auth\Login` | `authentication-shell` | retain auth shell |
| `lost-found.index` | `/lost-found` | `SearchDirectoryController` | `canonical-page` | retain `x-page-header` |
| `lost-found.create` | `/lost-found/new` | `SearchCaseCreateController` | `canonical-page` | retain `x-page-header` |
| `lost-found.show` | `/lost-found/{searchCase}` | `SearchCaseController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `lost-found.coordinate` | `/lost-found/{searchCase}/coordinate` | `SearchCoordinationController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `lost-found.poster` | `/lost-found/{searchCase}/poster` | `SearchPosterController` | `special-document-or-scoped-access` | retain isolated semantic document |
| `marketplace.index` | `/marketplace` | `ListingDirectoryController` | `canonical-page` | retain `x-page-header` |
| `marketplace.create` | `/marketplace/new` | `ListingCreateController` | `canonical-page` | retain `x-page-header` |
| `marketplace.show` | `/marketplace/{listing}` | `ListingController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `marketplace.orders.show` | `/marketplace/{listing}/orders/{order}` | `OrderController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `medical-access.show` | `/medical-access/{token}` | `MedicalSharedRecordController` | `special-document-or-scoped-access` | retain isolated semantic document |
| `medical-access.documents.download` | `/medical-access/{token}/documents/{medicalDocument}` | `MedicalSharedDocumentDownloadController` | `file-response` | no page identity |
| `medical-records.index` | `/medical-records` | `MedicalRecordDirectoryController` | `canonical-page` | retain `x-page-header` |
| `medical-records.create` | `/medical-records/new` | `MedicalRecordCreateController` | `canonical-page` | retain `x-page-header` |
| `medical-records.show` | `/medical-records/{medicalRecord}` | `MedicalRecordController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `medical-records.documents.download` | `/medical-records/{medicalRecord}/documents/{document}` | `MedicalDocumentDownloadController` | `file-response` | no page identity |
| `medical-records.emergency` | `/medical-records/{medicalRecord}/emergency` | `MedicalEmergencyCardController` | `special-document-or-scoped-access` | retain isolated semantic document |
| `medical-records.manage` | `/medical-records/{medicalRecord}/manage` | `MedicalRecordManageController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `meetups.index` | `/meetups` | `MeetupDirectoryPreviewController` | `canonical-page` | retain `x-page-header` |
| `meetups.small_dog_social` | `/meetups/small-dog-social` | `MeetupDetailPreviewController` | `canonical-page` | retain `x-page-header` |
| `meetups.show` | `/meetups/{event}` | `MeetupDetailPreviewController` | `canonical-page` | retain `x-page-header` |
| `meetups.created` | `/meetups/{item}` | `CreatedContentPreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `messages.index` | `/messages` | `MessageCenterPreviewController` | `canonical-page` | retain `x-page-header` |
| `messages.details` | `/messages/{conversation}/details` | `ConversationDetailPreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `neighbors.index` | `/neighbors` | `NeighborDirectoryPreviewController` | `canonical-page` | retain `x-page-header` |
| `neighbors.ari` | `/neighbors/ari-jensen` | `NeighborProfilePreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `notifications.index` | `/notifications` | `NotificationCenterPreviewController` | `canonical-page` | retain `x-page-header` |
| `organizations.index` | `/organizations` | `Organizations\OrganizationDirectory` | `canonical-page` | retain `x-page-header` |
| `organizations.invitations.respond` | `/organizations/invitations/{organizationInvitation:stable_key}/respond` | `Organizations\OrganizationInvitationResponse` | `canonical-page` | retain `x-page-header` |
| `organizations.show` | `/organizations/{organization:slug}` | `Organizations\OrganizationWorkspace` | `canonical-page` | retain `x-page-header` |
| `pets.index` | `/pets` | `PetDirectoryPreviewController` | `canonical-page` | retain `x-page-header` |
| `pets.manage.invitations` | `/pets/manage/invitations` | `Pets\PetProfileInvitations` | `canonical-page` | retain `x-page-header` |
| `pets.manage.create` | `/pets/manage/new` | `Pets\CreatePetProfile` | `canonical-page` | retain `x-page-header` |
| `pets.manage.show` | `/pets/manage/{petProfile:profile_key}` | `Pets\ManagePetProfile` | `canonical-page` | retain `x-page-header` |
| `pets.profile` | `/pets/profile/{petProfile:profile_key}` | `Pets\PublicPetProfile` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `pets.scout.legacy` | `/pets/scout` | `RedirectController` | `redirect` | no page identity |
| `pets.created` | `/pets/{item}` | `CreatedContentPreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `places.index` | `/places` | `PlaceDirectoryPreviewController` | `canonical-page` | retain `x-page-header` |
| `places.show` | `/places/{place}` | `PlaceDetailPreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `portal-media.show` | `/portal-media/{path}` | `PortalMediaController` | `file-response` | no page identity |
| `posts.show` | `/posts/{post}` | `PostThreadPreviewController` | `canonical-page` | retain `x-page-header` |
| `preview.feed` | `/preview/feed` | `PreviewController` | `canonical-page` | retain `x-page-header` |
| `profile.mia.legacy` | `/profile/mia-carter` | `RedirectController` | `redirect` | no page identity |
| `profile.settings` | `/profile/settings` | `ProfileSettings` | `canonical-page` | retain `x-page-header` |
| `register` | `/register` | `Auth\Register` | `authentication-shell` | retain auth shell |
| `password.reset` | `/reset-password/{token}` | `Auth\ResetPassword` | `authentication-shell` | retain auth shell |
| `share.show` | `/share/{target}` | `SharePreviewController` | `deliberate-detail-or-profile` | retain token-compatible hero pending audit |
| `verification.notice` | `/verify-email` | `Auth\VerifyEmail` | `authentication-shell` | retain auth shell |
| `verification.verify` | `/verify-email/{id}/{hash}` | `Auth\VerifyEmailController` | `redirect` | no page identity |
| `walks.index` | `/walks` | `WalkPlanPreviewController` | `canonical-page` | retain `x-page-header` |
