# Portal Route Matrix

The executable source of truth is `routes/web.php`; a fresh `php artisan
route:list --json` inventory reported 185 active routes on 2026-08-30. The
canonical `php artisan route:list --except-vendor --json` audit reported 174
first-party routes, including 119 routes that accept `GET`, and excluded 11
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

The 119 first-party routes accepting `GET` are classified below. The executable
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
| `admin.forum.index` | `/admin/forum` | `ForumAdministrationController` | `dashboard` | retain `x-page-header` |
| `bookings.show` | `/bookings/{booking}` | `BookingController` | `workspace` | retained by the completed exception audit below |
| `care-access.show` | `/care-access/{token}` | `CareSharedJournalController` | `shared access` | retain isolated semantic document |
| `care-access.media.download` | `/care-access/{token}/media/{careMedia}` | `CareSharedMediaDownloadController` | `shared access` | no page identity |
| `care-journals.index` | `/care-journals` | `CareJournalDirectoryController` | `directory` | retain `x-page-header` |
| `care-journals.create` | `/care-journals/new` | `CareJournalCreateController` | `editor` | retain `x-page-header` |
| `care-journals.show` | `/care-journals/{careJournal}` | `CareJournalController` | `workspace` | retained by the completed exception audit below |
| `care-journals.manage` | `/care-journals/{careJournal}/manage` | `CareJournalManageController` | `workspace` | retained by the completed exception audit below |
| `care-journals.media.download` | `/care-journals/{careJournal}/media/{careMedia}` | `CareMediaDownloadController` | `deliberate special case` | no page identity |
| `care-journals.report` | `/care-journals/{careJournal}/report` | `CareJournalReportController` | `print/export` | retain isolated semantic document |
| `circle.index` | `/circle` | `CirclePreviewController` | `directory` | retain `x-page-header` |
| `connections.index` | `/circle/connections` | `ConnectionCenterPreviewController` | `directory` | retain `x-page-header` |
| `pet-friends.index` | `/circle/pet-friends` | `PetFriendCenterPreviewController` | `directory` | retain `x-page-header` |
| `social.index` | `/circle/social` | `Social\RelationshipCenter` | `directory` | retain `x-page-header` |
| `compose` | `/compose/{kind}` | `ComposerController` | `editor` | retain `x-page-header` |
| `password.confirm` | `/confirm-password` | `Auth\ConfirmPassword` | `authentication` | retain auth shell |
| `consultations.show` | `/consultations/{consultation}` | `ConsultationController` | `workspace` | retained by the completed exception audit below |
| `content.index` | `/content` | `ContentFeedController` | `directory` | retain `x-page-header` |
| `content.show` | `/content/{contentPublication:publication_key}` | `ContentPublicationController` | `detail` | retained by the completed exception audit below |
| `device-access.show` | `/device-access/{token}` | `DeviceSharedDashboardController` | `shared access` | retain isolated semantic document |
| `devices.index` | `/devices` | `SmartDeviceDirectoryController` | `directory` | retain `x-page-header` |
| `devices.create` | `/devices/new` | `SmartDeviceCreateController` | `editor` | retain `x-page-header` |
| `devices.show` | `/devices/{smartDevice}` | `SmartDeviceController` | `dashboard` | retained by the completed exception audit below |
| `devices.manage` | `/devices/{smartDevice}/manage` | `SmartDeviceManageController` | `settings` | retained by the completed exception audit below |
| `discover.index` | `/discover` | `DiscoverPreviewController` | `directory` | database-backed recommendation hub using `x-page-header` |
| `experts.index` | `/experts` | `ExpertDirectoryController` | `directory` | retain `x-page-header` |
| `experts.create` | `/experts/new` | `ExpertProfileCreateController` | `editor` | retain `x-page-header` |
| `experts.dashboard` | `/experts/workspace` | `ExpertDashboardController` | `dashboard` | retain `x-page-header` |
| `experts.show` | `/experts/{expertProfile}` | `ExpertProfileController` | `detail` | retained by the completed exception audit below |
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
| `groups.apartment_pets` | `/groups/apartment-pets-pdx` | `GroupDetailPreviewController` | `detail` | retained by the completed exception audit below |
| `groups.show` | `/groups/{group}` | `GroupDetailPreviewController` | `detail` | retained by the completed exception audit below |
| `groups.created` | `/groups/{item}` | `CreatedContentPreviewController` | `detail` | retained by the completed exception audit below |
| `knowledge.index` | `/knowledge` | `KnowledgeController` | `directory` | retain `x-page-header` |
| `knowledge.guides.create` | `/knowledge/guides/new` | `KnowledgeGuideCreateController` | `editor` | retain `x-page-header` |
| `knowledge.articles.show` | `/knowledge/{knowledgeArticle}` | `ArticleController` | `detail` | retained by the completed exception audit below |
| `knowledge.guides.edit` | `/knowledge/{knowledgeArticle}/edit` | `KnowledgeGuideEditController` | `editor` | retain `x-page-header` |
| `knowledge.articles.export` | `/knowledge/{knowledgeArticle}/export` | `KnowledgeGuideExportController` | `print/export` | no page identity |
| `knowledge.articles.print` | `/knowledge/{knowledgeArticle}/print` | `KnowledgeGuidePrintController` | `print/export` | retain isolated semantic document |
| `knowledge.guides.translations.create` | `/knowledge/{knowledgeArticle}/translations/new` | `KnowledgeGuideTranslationCreateController` | `editor` | retain `x-page-header` |
| `login` | `/login` | `Auth\Login` | `authentication` | retain auth shell |
| `lost-found.index` | `/lost-found` | `SearchDirectoryController` | `directory` | retain `x-page-header` |
| `lost-found.create` | `/lost-found/new` | `SearchCaseCreateController` | `editor` | retain `x-page-header` |
| `lost-found.show` | `/lost-found/{searchCase}` | `SearchCaseController` | `detail` | retained by the completed exception audit below |
| `lost-found.coordinate` | `/lost-found/{searchCase}/coordinate` | `SearchCoordinationController` | `workspace` | retained by the completed exception audit below |
| `lost-found.poster` | `/lost-found/{searchCase}/poster` | `SearchPosterController` | `print/export` | retain isolated semantic document |
| `marketplace.index` | `/marketplace` | `ListingDirectoryController` | `directory` | retain `x-page-header` |
| `marketplace.create` | `/marketplace/new` | `ListingCreateController` | `editor` | retain `x-page-header` |
| `members.show` | `/members/{socialActor:actor_key}` | `MemberProfileController` | `detail` | dynamic minimized member profile using canonical `x-page-header` |
| `marketplace.show` | `/marketplace/{listing}` | `ListingController` | `detail` | retained by the completed exception audit below |
| `marketplace.orders.show` | `/marketplace/{listing}/orders/{order}` | `OrderController` | `workspace` | retained by the completed exception audit below |
| `medical-access.show` | `/medical-access/{token}` | `MedicalSharedRecordController` | `shared access` | retain isolated semantic document |
| `medical-access.documents.download` | `/medical-access/{token}/documents/{medicalDocument}` | `MedicalSharedDocumentDownloadController` | `shared access` | no page identity |
| `medical-records.index` | `/medical-records` | `MedicalRecordDirectoryController` | `directory` | retain `x-page-header` |
| `medical-records.create` | `/medical-records/new` | `MedicalRecordCreateController` | `editor` | retain `x-page-header` |
| `medical-records.show` | `/medical-records/{medicalRecord}` | `MedicalRecordController` | `workspace` | retained by the completed exception audit below |
| `medical-records.documents.download` | `/medical-records/{medicalRecord}/documents/{document}` | `MedicalDocumentDownloadController` | `deliberate special case` | no page identity |
| `medical-records.emergency` | `/medical-records/{medicalRecord}/emergency` | `MedicalEmergencyCardController` | `print/export` | retain isolated semantic document |
| `medical-records.manage` | `/medical-records/{medicalRecord}/manage` | `MedicalRecordManageController` | `workspace` | retained by the completed exception audit below |
| `meetups.index` | `/meetups` | `MeetupDirectoryPreviewController` | `directory` | retain `x-page-header` |
| `meetups.show` | `/meetups/{event}` | `MeetupDetailPreviewController` | `detail` | retain `x-page-header` |
| `meetups.created` | `/meetups/{item}` | `CreatedContentPreviewController` | `detail` | retained by the completed exception audit below |
| `messages.index` | `/messages` | `MessageCenterPreviewController` | `workspace` | retain `x-page-header` |
| `neighbors.index` | `/neighbors` | `NeighborDirectoryPreviewController` | `directory` | retain `x-page-header` |
| `notifications.index` | `/notifications` | `NotificationCenterPreviewController` | `directory` | retain `x-page-header` |
| `onboarding.show` | `/onboarding` | `Onboarding` | `authentication` | dedicated authenticated account-flow shell with resumable server state |
| `organizations.index` | `/organizations` | `Organizations\OrganizationDirectory` | `directory` | retain `x-page-header` |
| `organizations.invitations.respond` | `/organizations/invitations/{organizationInvitation:stable_key}/respond` | `Organizations\OrganizationInvitationResponse` | `editor` | retain `x-page-header` |
| `organizations.show` | `/organizations/{organization:slug}` | `Organizations\OrganizationWorkspace` | `workspace` | retain `x-page-header` |
| `pets.index` | `/pets` | `PetProfileWorkspaceController` | `directory` | authenticated personal workspace; Eloquent search/filter/pagination and canonical `x-page-header` |
| `pets.manage.invitations` | `/pets/manage/invitations` | `Pets\PetProfileInvitations` | `workspace` | retain `x-page-header` |
| `pets.manage.access-requests` | `/pets/manage/{petProfile:profile_key}/access-requests` | `Pets\PetProfileAccessRequests` | `workspace` | retain `x-page-header` and policy-protected manager review |
| `pets.manage.create` | `/pets/manage/new` | `Pets\CreatePetProfile` | `editor` | retain `x-page-header` |
| `pets.manage.show` | `/pets/manage/{petProfile:profile_key}` | `Pets\ManagePetProfile` | `editor` | retain `x-page-header` |
| `pets.media.show` | `/pets/profile/{petProfile:profile_key}/media/{petProfileMedia:media_key}` | `PetProfileMediaController` | `deliberate special case` | no page identity |
| `pets.profile` | `/pets/profile/{petProfile:profile_key}` | `Pets\PublicPetProfile` | `detail` | retained by the completed exception audit below |
| `places.index` | `/places` | `PlaceDirectoryPreviewController` | `directory` | retain `x-page-header`; policy-scoped persisted catalogue |
| `places.moderation.submissions` | `/places/moderation/submissions` | `Places\PlaceModerationWorkspace` | `dashboard` | authorized moderator queue with canonical page identity |
| `places.media.show` | `/places/{place:slug}/media/{placeMedia:media_key}/{variant}` | `PlaceMediaController` | `deliberate special case` | authenticated contained media response; no document identity |
| `places.show` | `/places/{place}` | `PlaceDetailPreviewController` | `detail` | stable dynamic slug; public-safe projection only |
| `places.submissions.create` | `/places/submissions/new` | `Places\CreatePlaceSubmission` | `editor` | validated member submission workspace |
| `places.submissions.show` | `/places/submissions/{placeSubmission}` | `Places\PlaceSubmissionStatusPage` | `workspace` | submitter-scoped durable review status |
| `portal-media.show` | `/portal-media/{path}` | `PortalMediaController` | `deliberate special case` | no page identity |
| `posts.show` | `/posts/{post}` | `PostThreadPreviewController` | `detail` | retain `x-page-header` |
| `preview.feed` | `/preview/feed` | `PreviewController` | `directory` | retain `x-page-header` |
| `profile.settings` | `/profile/settings` | `ProfileSettings` | `settings` | retain `x-page-header` |
| `register` | `/register` | `Auth\Register` | `authentication` | retain auth shell |
| `password.reset` | `/reset-password/{token}` | `Auth\ResetPassword` | `authentication` | retain auth shell |
| `share.show` | `/share/{target}` | `SharePreviewController` | `detail` | verified token-compatible hero, localized share projection, and canonical icon controls |
| `verification.notice` | `/verify-email` | `Auth\VerifyEmail` | `authentication` | retain auth shell |
| `verification.verify` | `/verify-email/{id}/{hash}` | `Auth\VerifyEmailController` | `authentication` | no page identity |
| `walks.index` | `/walks` | `WalkPlanPreviewController` | `directory` | retain `x-page-header` |

## Completed Detail And Workspace Exception Audit

The table below is the explicit exception disposition required by
`PRD-UI-001`. A canonical `x-page-header` remains valid on a route classified
as `detail` or `workspace`; the route class describes the task, not a visual
waiver. A resource-led identity is retained only where entity media, private
scope, lifecycle state, ownership, or operational status must be read together
with the title. All retained identities render one `h1`; ordinary back links
use `x-detail-navigation` so their already-authorized destination is preserved
inside one labelled, keyboard-reachable navigation contract.

| Route | Current identity owner | Disposition and semantic reason |
| --- | --- | --- |
| `content.show` | `x-content-publication-card`, `data-content-detail-identity` | Retain: the publication document itself is the identity surface; a localized untitled fallback now guarantees one non-empty `h1`. |
| `experts.show` | expert profile hero, `data-expert-detail-identity` | Retain: avatar, professional type, independent verification, scope, and authorized actions are inseparable profile identity. |
| `forum.topics.show` | `x-page-header` | Canonical header; topic status and actions are prepared metadata, so no bespoke hero is needed. |
| `groups.apartment_pets` | `x-group-hero` | Retain: cover media, privacy/category badges, membership action, and group metrics are resource-led identity. |
| `groups.show` | `x-group-hero` | Retain for the same group semantics; dynamic slug and active tab behavior remain unchanged. |
| `groups.created` | `x-created-content-detail` → `x-detail-page` / `x-detail-hero` | Retain: the created-resource confirmation hero communicates target identity and next actions. |
| `knowledge.articles.show` | knowledge article document header | Retain: document lifecycle, authorship, language, review state, and print/export actions are article identity. |
| `lost-found.show` | case hero, `data-lost-found-detail-identity` | Retain: public case code, urgency, status, last-seen context, and coordination actions are safety-critical identity. |
| `marketplace.show` | listing hero, `data-marketplace-detail-identity` | Retain: media, price, seller/status, safety information, and transaction actions are listing identity. |
| `meetups.show` | `livewire:forum.forum-event-workspace` with `x-page-header` | Canonical event header inside the database-backed workspace; no parallel hero. |
| `meetups.created` | `x-created-content-detail` → `x-detail-page` / `x-detail-hero` | Retain the created-resource confirmation semantics. |
| `members.show` | `x-page-header` | Canonical minimized member identity; policy and block-scoped profile data remain prepared upstream. |
| `pets.profile` | public-pet profile header | Retain: canonical pet identity, manager-safe public projection, media, and privacy state differ from a normal header. |
| `places.show` | `x-place-hero`, `data-place-detail-hero` | Retain: place media, public-safe location, open state, verification, warnings, and route actions are resource identity. |
| `posts.show` | `x-page-header` | Canonical post-thread identity; thread content remains below the shared header. |
| `share.show` | `x-detail-page` / `x-context-hero` | Retain: the explicitly selected share target and privacy boundary are the page identity. |
| `bookings.show` | booking workspace header plus `x-detail-navigation` | Retain: appointment reference, payment/lifecycle state, participant scope, and available operation are transactional identity. |
| `care-journals.manage` | care management header plus `x-detail-navigation` | Retain: encrypted owner workspace, selected pet journal, and management capability are private operational identity. |
| `care-journals.show` | care record header, `data-care-journal-workspace-identity` | Retain: selected pet, current caregiver, private scope, and journal actions are workspace identity. |
| `consultations.show` | consultation header plus `x-detail-navigation` | Retain: booked consultation, emergency boundary, live-room state, and outcome controls are operational identity. |
| `forum.expert-sessions.show` | Livewire workspace with `x-page-header` | Canonical header inside the authorized session workspace. |
| `forum.groups.show` | Livewire group workspace with `x-page-header` | Canonical header inside the membership- and role-scoped workspace. |
| `forum.mentorship.index` | Livewire mentorship workspace with `x-page-header` | Canonical header; derived matching state remains prepared in the component. |
| `lost-found.coordinate` | coordination header plus `x-detail-navigation` | Retain: private case coordination state and safety actions differ from the public case hero. |
| `marketplace.orders.show` | order workspace header plus `x-detail-navigation` | Retain: immutable order/listing identity, payment status, and participant actions are transactional identity. |
| `medical-records.manage` | medical management header plus `x-detail-navigation` | Retain: encrypted owner controls and selected pet record are private operational identity. |
| `medical-records.show` | medical record header, `data-medical-record-workspace-identity` | Retain: selected pet, privacy, clinical summary, and protected record actions are workspace identity. |
| `messages.index` | shared messages view with `x-page-header` | Canonical header; nine-folder order remains below it and above the messaging shell. |
| `organizations.show` | Livewire organization workspace with `x-page-header` | Canonical header inside policy-scoped organization operations. |
| `pets.manage.access-requests` | Livewire manager workspace with `x-page-header` | Canonical header; pet binding and manager authorization remain server authoritative. |
| `pets.manage.invitations` | Livewire invitation workspace with `x-page-header` | Canonical header; recipient-scoped invitation state remains below it. |
| `places.submissions.show` | Livewire submission status workspace with `x-page-header` | Canonical header; durable review status and submitter authorization remain component-owned. |
