# Portal Route Matrix

The executable source of truth is `routes/web.php`; a fresh `php artisan
route:list --json` inventory reported 184 active routes on 2026-08-30. The
canonical `php artisan route:list --except-vendor --json` audit reported 173
first-party routes, including 118 routes that accept `GET`, and excluded 11
package/runtime endpoints. The executable ledger and the matrix below cover
each named first-party `GET` route exactly once.

## Event Routes

The four meetup routes remain active and appear once in the global matrix
below. None was removed or redirected by page-identity work. Product access is
guarded by the route middleware and resource policy; compatibility flows keep
their canonical event targets. Behaviour coverage remains mapped in
`tests/Support/route-coverage.php`.

## Discovery Routes

| Name | Method | URI | Runtime owner | Purpose |
| --- | --- | --- | --- | --- |
| `discover.index` | GET | `/discover` | `DiscoverPreviewController` | validated, bounded, explainable recommendations |
| `discover.preferences.store` | POST | `/discover/preferences` | `DiscoveryPreferenceController` | policy-scoped item/category hide or reset |
| `members.show` | GET | `/members/{socialActor:actor_key}` | `MemberProfileController` | minimized policy- and block-scoped member destination |

Discovery routes remain inside the authenticated application shell; member
detail is a public discoverable profile with independent policy and block
checks. The mutation is throttled, independently validated and authorized, and redirects to the
canonical GET state. See `docs/portal/discovery.md` for privacy and destination
boundaries.

## Global Page Identity Classification

The 118 first-party routes accepting `GET` are classified below. The executable
one-route/one-class ledger is
`tests/Support/page-identity-route-classification.php`; the route inventory test
fails when a route is added, removed, duplicated, or left unclassified.

| Class | Page identity decision | Primary verification |
| --- | --- | --- |
| `directory` | Use the canonical `x-page-header` directory identity | `PageIdentityStandardizationTest` plus module tests |
| `detail` | Use `x-page-header` or a documented resource-led hero when media, status, or profile semantics differ from a normal header | Structural exception audit plus module tests |
| `workspace` | Use one canonical header or a documented task/resource workspace identity | Structural exception audit plus authorization and workflow tests |
| `editor` | Use the canonical `x-page-header` above the prepared form workflow | Page-identity, validation, and direct-action tests |
| `dashboard` | Use the canonical `x-page-header` above bounded operational summaries | Page-identity and query-budget tests |
| `settings` | Use one canonical settings identity above authorized controls | Page-identity and authorization tests |
| `authentication` | Keep the isolated `x-auth-page-header` hierarchy, or no identity for the signed redirect endpoint | Authentication and account-entry browser checks |
| `shared access` | Keep a scoped semantic document or file response without the portal header | Domain privacy, token, and containment tests |
| `print/export` | Keep its print/poster/emergency document identity or file response | Domain privacy, document, and export tests |
| `deliberate special case` | Keep the documented file, structured, redirect, or semantically distinct response contract | Route, containment, and access-boundary tests |

The runtime owner is the controller or class-based Livewire component. It owns
the prepared view data and remains the lookup point for the current template;
the decision column is the desired page-identity boundary.

| Name | URI | Runtime owner | Class | Identity component or audited exception |
| --- | --- | --- | --- | --- |
| `home` | `/` | `HomeController` | `deliberate special case` | no page identity |
| `profile.mia` | `/@mia-carter` | `MemberProfilePreviewController` | `detail` | verified localized token-compatible hero with stable tab/audience codes |
| `pets.nori` | `/@mia-carter/nori` | `PetProfilePreviewController` | `detail` | retain token-compatible hero pending audit |
| `pets.scout` | `/@mia-carter/scout` | `PetProfilePreviewController` | `detail` | retain token-compatible hero pending audit |
| `admin.forum.index` | `/admin/forum` | `ForumAdministrationController` | `dashboard` | retain `x-page-header` |
| `bookings.show` | `/bookings/{booking}` | `BookingController` | `workspace` | retain token-compatible hero pending audit |
| `care-access.show` | `/care-access/{token}` | `CareSharedJournalController` | `shared access` | retain isolated semantic document |
| `care-access.media.download` | `/care-access/{token}/media/{careMedia}` | `CareSharedMediaDownloadController` | `shared access` | no page identity |
| `care-journals.index` | `/care-journals` | `CareJournalDirectoryController` | `directory` | retain `x-page-header` |
| `care-journals.create` | `/care-journals/new` | `CareJournalCreateController` | `editor` | retain `x-page-header` |
| `care-journals.show` | `/care-journals/{careJournal}` | `CareJournalController` | `workspace` | retain token-compatible hero pending audit |
| `care-journals.manage` | `/care-journals/{careJournal}/manage` | `CareJournalManageController` | `workspace` | retain token-compatible hero pending audit |
| `care-journals.media.download` | `/care-journals/{careJournal}/media/{careMedia}` | `CareMediaDownloadController` | `deliberate special case` | no page identity |
| `care-journals.report` | `/care-journals/{careJournal}/report` | `CareJournalReportController` | `print/export` | retain isolated semantic document |
| `circle.index` | `/circle` | `CirclePreviewController` | `directory` | retain `x-page-header` |
| `connections.index` | `/circle/connections` | `ConnectionCenterPreviewController` | `directory` | retain `x-page-header` |
| `pet-friends.index` | `/circle/pet-friends` | `PetFriendCenterPreviewController` | `directory` | retain `x-page-header` |
| `social.index` | `/circle/social` | `Social\RelationshipCenter` | `directory` | retain `x-page-header` |
| `compose` | `/compose/{kind}` | `ComposerController` | `editor` | retain `x-page-header` |
| `password.confirm` | `/confirm-password` | `Auth\ConfirmPassword` | `authentication` | retain auth shell |
| `consultations.show` | `/consultations/{consultation}` | `ConsultationController` | `workspace` | retain token-compatible hero pending audit |
| `content.index` | `/content` | `ContentFeedController` | `directory` | retain `x-page-header` |
| `content.show` | `/content/{contentPublication:publication_key}` | `ContentPublicationController` | `detail` | retain token-compatible hero pending audit |
| `device-access.show` | `/device-access/{token}` | `DeviceSharedDashboardController` | `shared access` | retain isolated semantic document |
| `devices.index` | `/devices` | `SmartDeviceDirectoryController` | `directory` | retain `x-page-header` |
| `devices.create` | `/devices/new` | `SmartDeviceCreateController` | `editor` | retain `x-page-header` |
| `devices.show` | `/devices/{smartDevice}` | `SmartDeviceController` | `dashboard` | retain token-compatible hero pending audit |
| `devices.manage` | `/devices/{smartDevice}/manage` | `SmartDeviceManageController` | `settings` | retain token-compatible hero pending audit |
| `discover.index` | `/discover` | `DiscoverPreviewController` | `directory` | database-backed recommendation hub using `x-page-header` |
| `experts.index` | `/experts` | `ExpertDirectoryController` | `directory` | retain `x-page-header` |
| `experts.create` | `/experts/new` | `ExpertProfileCreateController` | `editor` | retain `x-page-header` |
| `experts.dashboard` | `/experts/workspace` | `ExpertDashboardController` | `dashboard` | retain `x-page-header` |
| `experts.show` | `/experts/{expertProfile}` | `ExpertProfileController` | `detail` | retain token-compatible hero pending audit |
| `experts.bookings.create` | `/experts/{expertProfile}/book` | `BookingCreateController` | `editor` | retain `x-page-header` |
| `experts.edit` | `/experts/{expertProfile}/edit` | `ExpertProfileEditController` | `editor` | retain `x-page-header` |
| `password.request` | `/forgot-password` | `Auth\ForgotPassword` | `authentication` | retain auth shell |
| `forum.index` | `/forum` | `ForumController` | `directory` | retain `x-page-header` |
| `forum.topics.create` | `/forum/ask` | `TopicCreateController` | `editor` | retain `x-page-header` |
| `forum.expert-sessions.index` | `/forum/expert-sessions` | `ForumExpertSessionDirectoryController` | `directory` | retain `x-page-header` |
| `forum.expert-sessions.show` | `/forum/expert-sessions/{forumExpertSession:stable_key}` | `ForumExpertSessionShowController` | `workspace` | retain `x-page-header` |
| `forum.groups.index` | `/forum/groups` | `ForumGroupDirectoryController` | `directory` | retain `x-page-header` |
| `forum.groups.show` | `/forum/groups/{forumGroup:stable_key}` | `ForumGroupShowController` | `workspace` | retain `x-page-header` |
| `forum.groups.files.download` | `/forum/groups/{forumGroup:stable_key}/files/{file:stable_key}` | `ForumGroupFileDownloadController` | `deliberate special case` | no page identity |
| `forum.journals.index` | `/forum/journals` | `ForumJournalDirectoryController` | `directory` | retain `x-page-header` |
| `forum.journals.export` | `/forum/journals/{forumJournal:stable_key}/export` | `ForumJournalExportController` | `print/export` | no page identity |
| `forum.journals.media.show` | `/forum/journals/{forumJournal:stable_key}/media/{forumJournalMedia:stable_key}` | `ForumJournalMediaController` | `deliberate special case` | no page identity |
| `forum.mentorship.index` | `/forum/mentorship` | `ForumMentorshipController` | `workspace` | retain `x-page-header` |
| `forum.topics.similar` | `/forum/similar` | `SimilarTopicController` | `deliberate special case` | no page identity |
| `forum.topics.show` | `/forum/topics/{forumTopic}` | `TopicController` | `detail` | retain `x-page-header` |
| `forum.topics.edit` | `/forum/topics/{forumTopic}/edit` | `TopicEditController` | `editor` | retain `x-page-header` |
| `groups.index` | `/groups` | `GroupDirectoryPreviewController` | `directory` | retain `x-page-header` |
| `groups.apartment_pets` | `/groups/apartment-pets-pdx` | `GroupDetailPreviewController` | `detail` | retain token-compatible hero pending audit |
| `groups.show` | `/groups/{group}` | `GroupDetailPreviewController` | `detail` | retain token-compatible hero pending audit |
| `groups.created` | `/groups/{item}` | `CreatedContentPreviewController` | `detail` | retain token-compatible hero pending audit |
| `knowledge.index` | `/knowledge` | `KnowledgeController` | `directory` | retain `x-page-header` |
| `knowledge.guides.create` | `/knowledge/guides/new` | `KnowledgeGuideCreateController` | `editor` | retain `x-page-header` |
| `knowledge.articles.show` | `/knowledge/{knowledgeArticle}` | `ArticleController` | `detail` | retain token-compatible hero pending audit |
| `knowledge.guides.edit` | `/knowledge/{knowledgeArticle}/edit` | `KnowledgeGuideEditController` | `editor` | retain `x-page-header` |
| `knowledge.articles.export` | `/knowledge/{knowledgeArticle}/export` | `KnowledgeGuideExportController` | `print/export` | no page identity |
| `knowledge.articles.print` | `/knowledge/{knowledgeArticle}/print` | `KnowledgeGuidePrintController` | `print/export` | retain isolated semantic document |
| `knowledge.guides.translations.create` | `/knowledge/{knowledgeArticle}/translations/new` | `KnowledgeGuideTranslationCreateController` | `editor` | retain `x-page-header` |
| `login` | `/login` | `Auth\Login` | `authentication` | retain auth shell |
| `lost-found.index` | `/lost-found` | `SearchDirectoryController` | `directory` | retain `x-page-header` |
| `lost-found.create` | `/lost-found/new` | `SearchCaseCreateController` | `editor` | retain `x-page-header` |
| `lost-found.show` | `/lost-found/{searchCase}` | `SearchCaseController` | `detail` | retain token-compatible hero pending audit |
| `lost-found.coordinate` | `/lost-found/{searchCase}/coordinate` | `SearchCoordinationController` | `workspace` | retain token-compatible hero pending audit |
| `lost-found.poster` | `/lost-found/{searchCase}/poster` | `SearchPosterController` | `print/export` | retain isolated semantic document |
| `marketplace.index` | `/marketplace` | `ListingDirectoryController` | `directory` | retain `x-page-header` |
| `marketplace.create` | `/marketplace/new` | `ListingCreateController` | `editor` | retain `x-page-header` |
| `members.show` | `/members/{socialActor:actor_key}` | `MemberProfileController` | `detail` | dynamic minimized member profile using canonical `x-page-header` |
| `marketplace.show` | `/marketplace/{listing}` | `ListingController` | `detail` | retain token-compatible hero pending audit |
| `marketplace.orders.show` | `/marketplace/{listing}/orders/{order}` | `OrderController` | `workspace` | retain token-compatible hero pending audit |
| `medical-access.show` | `/medical-access/{token}` | `MedicalSharedRecordController` | `shared access` | retain isolated semantic document |
| `medical-access.documents.download` | `/medical-access/{token}/documents/{medicalDocument}` | `MedicalSharedDocumentDownloadController` | `shared access` | no page identity |
| `medical-records.index` | `/medical-records` | `MedicalRecordDirectoryController` | `directory` | retain `x-page-header` |
| `medical-records.create` | `/medical-records/new` | `MedicalRecordCreateController` | `editor` | retain `x-page-header` |
| `medical-records.show` | `/medical-records/{medicalRecord}` | `MedicalRecordController` | `workspace` | retain token-compatible hero pending audit |
| `medical-records.documents.download` | `/medical-records/{medicalRecord}/documents/{document}` | `MedicalDocumentDownloadController` | `deliberate special case` | no page identity |
| `medical-records.emergency` | `/medical-records/{medicalRecord}/emergency` | `MedicalEmergencyCardController` | `print/export` | retain isolated semantic document |
| `medical-records.manage` | `/medical-records/{medicalRecord}/manage` | `MedicalRecordManageController` | `workspace` | retain token-compatible hero pending audit |
| `meetups.index` | `/meetups` | `MeetupDirectoryPreviewController` | `directory` | retain `x-page-header` |
| `meetups.small_dog_social` | `/meetups/small-dog-social` | `MeetupDetailPreviewController` | `detail` | retain `x-page-header` |
| `meetups.show` | `/meetups/{event}` | `MeetupDetailPreviewController` | `detail` | retain `x-page-header` |
| `meetups.created` | `/meetups/{item}` | `CreatedContentPreviewController` | `detail` | retain token-compatible hero pending audit |
| `messages.index` | `/messages` | `MessageCenterPreviewController` | `workspace` | retain `x-page-header` |
| `messages.details` | `/messages/{conversation}/details` | `ConversationDetailPreviewController` | `workspace` | retain token-compatible hero pending audit |
| `neighbors.index` | `/neighbors` | `NeighborDirectoryPreviewController` | `directory` | retain `x-page-header` |
| `neighbors.ari` | `/neighbors/ari-jensen` | `NeighborProfilePreviewController` | `detail` | verified profile-led hero, dedicated EN/LT/RU presenter, passive Blade, canonical icons, and responsive browser ratchet |
| `notifications.index` | `/notifications` | `NotificationCenterPreviewController` | `directory` | retain `x-page-header` |
| `organizations.index` | `/organizations` | `Organizations\OrganizationDirectory` | `directory` | retain `x-page-header` |
| `organizations.invitations.respond` | `/organizations/invitations/{organizationInvitation:stable_key}/respond` | `Organizations\OrganizationInvitationResponse` | `editor` | retain `x-page-header` |
| `organizations.show` | `/organizations/{organization:slug}` | `Organizations\OrganizationWorkspace` | `workspace` | retain `x-page-header` |
| `pets.index` | `/pets` | `PetProfileWorkspaceController` | `directory` | authenticated personal workspace; Eloquent search/filter/pagination and canonical `x-page-header` |
| `pets.manage.invitations` | `/pets/manage/invitations` | `Pets\PetProfileInvitations` | `workspace` | retain `x-page-header` |
| `pets.manage.access-requests` | `/pets/manage/{petProfile:profile_key}/access-requests` | `Pets\PetProfileAccessRequests` | `workspace` | retain `x-page-header` and policy-protected manager review |
| `pets.manage.create` | `/pets/manage/new` | `Pets\CreatePetProfile` | `editor` | retain `x-page-header` |
| `pets.manage.show` | `/pets/manage/{petProfile:profile_key}` | `Pets\ManagePetProfile` | `editor` | retain `x-page-header` |
| `pets.media.show` | `/pets/profile/{petProfile:profile_key}/media/{petProfileMedia:media_key}` | `PetProfileMediaController` | `deliberate special case` | no page identity |
| `pets.profile` | `/pets/profile/{petProfile:profile_key}` | `Pets\PublicPetProfile` | `detail` | retain token-compatible hero pending audit |
| `pets.scout.legacy` | `/pets/scout` | `RedirectController` | `deliberate special case` | no page identity |
| `pets.created` | `/pets/{item}` | `CreatedContentPreviewController` | `detail` | retain token-compatible hero pending audit |
| `places.index` | `/places` | `PlaceDirectoryPreviewController` | `directory` | retain `x-page-header`; policy-scoped persisted catalogue |
| `places.moderation.submissions` | `/places/moderation/submissions` | `Places\PlaceModerationWorkspace` | `dashboard` | authorized moderator queue with canonical page identity |
| `places.media.show` | `/places/{place:slug}/media/{placeMedia:media_key}/{variant}` | `PlaceMediaController` | `deliberate special case` | authenticated contained media response; no document identity |
| `places.show` | `/places/{place}` | `PlaceDetailPreviewController` | `detail` | stable dynamic slug; public-safe projection only |
| `places.submissions.create` | `/places/submissions/new` | `Places\CreatePlaceSubmission` | `editor` | validated member submission workspace |
| `places.submissions.show` | `/places/submissions/{placeSubmission}` | `Places\PlaceSubmissionStatusPage` | `workspace` | submitter-scoped durable review status |
| `portal-media.show` | `/portal-media/{path}` | `PortalMediaController` | `deliberate special case` | no page identity |
| `posts.show` | `/posts/{post}` | `PostThreadPreviewController` | `detail` | retain `x-page-header` |
| `preview.feed` | `/preview/feed` | `PreviewController` | `directory` | retain `x-page-header` |
| `profile.mia.legacy` | `/profile/mia-carter` | `RedirectController` | `deliberate special case` | no page identity |
| `profile.settings` | `/profile/settings` | `ProfileSettings` | `settings` | retain `x-page-header` |
| `register` | `/register` | `Auth\Register` | `authentication` | retain auth shell |
| `password.reset` | `/reset-password/{token}` | `Auth\ResetPassword` | `authentication` | retain auth shell |
| `share.show` | `/share/{target}` | `SharePreviewController` | `detail` | verified token-compatible hero, localized share projection, and canonical icon controls |
| `verification.notice` | `/verify-email` | `Auth\VerifyEmail` | `authentication` | retain auth shell |
| `verification.verify` | `/verify-email/{id}/{hash}` | `Auth\VerifyEmailController` | `authentication` | no page identity |
| `walks.index` | `/walks` | `WalkPlanPreviewController` | `directory` | retain `x-page-header` |
