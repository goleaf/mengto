<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\CredentialType;
use App\Models\AuditLog;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Credential;
use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\Publication;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ExpertPresenter
{
    private const int DASHBOARD_BOOKING_LIMIT = 12;

    private const int DASHBOARD_CREDENTIAL_LIMIT = 8;

    private const int DASHBOARD_SERVICE_LIMIT = 12;

    public function __construct(
        private readonly ForumActor $actor,
        private readonly ExpertTaxonomy $taxonomy,
        private readonly LocaleFormatter $formatter,
    ) {}

    /** @param array<string, mixed> $filters */
    public function directory(array $filters): array
    {
        $query = ExpertProfile::query()
            ->forDirectory()
            ->published()
            ->search($filters['q'] ?? null)
            ->forSpecies($filters['species'] ?? null)
            ->forSpecialization($filters['specialization'] ?? null)
            ->forFormat($filters['format'] ?? null)
            ->when(filled($filters['type'] ?? null), fn (Builder $builder): Builder => $builder->where('primary_type', $filters['type']))
            ->when(filled($filters['city'] ?? null), fn (Builder $builder): Builder => $builder->where('city', $filters['city']))
            ->when(filled($filters['language'] ?? null), fn (Builder $builder): Builder => $builder->whereJsonContains('languages', $filters['language']))
            ->when(filled($filters['accessible'] ?? null), fn (Builder $builder): Builder => $builder->whereJsonContains('accessibility', $filters['accessible']))
            ->when((bool) ($filters['verified'] ?? false), fn (Builder $builder): Builder => $builder->where('qualification_verified', true))
            ->when(
                ($filters['availability'] ?? null) === 'available',
                fn (Builder $builder): Builder => $builder->where('accepts_new_clients', true),
            )
            ->when(
                ($filters['availability'] ?? null) === 'today',
                fn (Builder $builder): Builder => $builder->whereDate('next_available_at', today()),
            )
            ->when(
                ($filters['availability'] ?? null) === 'week',
                fn (Builder $builder): Builder => $builder->whereBetween('next_available_at', [now(), now()->addWeek()]),
            )
            ->when(
                ($filters['availability'] ?? null) === 'waitlist',
                fn (Builder $builder): Builder => $builder->where('availability_status', 'waitlist'),
            )
            ->withExists([
                'engagements as is_saved' => fn ($engagements) => $engagements
                    ->where('user_key', $this->actor->key())
                    ->where('is_saved', true),
            ]);

        $this->applySort($query, (string) ($filters['sort'] ?? 'relevance'));

        $experts = $query->simplePaginate(9)->withQueryString();
        $experts->through(fn (ExpertProfile $profile): array => $this->card($profile, $filters));

        return [
            'page_title' => __('messages.expert_community'),
            'active_section' => 'experts',
            'experts' => $experts,
            'filters' => $filters,
            'types' => $this->taxonomy->types(),
            'species_options' => $this->taxonomy->species(),
            'specializations' => $this->taxonomy->specializations(),
            'formats' => $this->taxonomy->formats(),
            'languages' => $this->taxonomy->languages(),
            'availability_options' => $this->taxonomy->availability(),
            'sort_options' => $this->taxonomy->sortOptions(),
            'stats' => $this->stats(),
        ];
    }

    /** @return array<string, mixed> */
    public function profile(ExpertProfile $profile): array
    {
        $profile->load([
            'credentials' => fn ($credentials) => $credentials
                ->select([
                    'id', 'expert_profile_id', 'type', 'title', 'issuer', 'region',
                    'number_last_four', 'issued_at', 'expires_at', 'status',
                    'verified_at',
                ])
                ->orderByDesc('verified_at'),
            'services' => fn ($services) => $services
                ->select([
                    'id', 'expert_profile_id', 'slug', 'name', 'type', 'format',
                    'description', 'duration_minutes', 'price', 'currency',
                    'pricing_model', 'includes', 'excludes', 'preparation',
                    'cancellation_policy', 'follow_up_days', 'requires_payment',
                    'requires_approval', 'capacity', 'status',
                ])
                ->active()
                ->orderBy('price'),
            'availabilitySlots' => fn ($slots) => $slots
                ->select([
                    'id', 'expert_profile_id', 'service_id', 'starts_at', 'ends_at',
                    'timezone', 'format', 'location_label', 'capacity',
                    'booked_count', 'status',
                ])
                ->open()
                ->orderBy('starts_at'),
            'publications' => fn ($publications) => $publications
                ->select([
                    'id', 'expert_profile_id', 'slug', 'title', 'summary', 'type',
                    'category', 'tags', 'sources', 'conflict_disclosure', 'language',
                    'status', 'last_reviewed_at', 'published_at',
                ])
                ->published()
                ->latest('published_at'),
            'reviews' => fn ($reviews) => $reviews
                ->select([
                    'id', 'expert_profile_id', 'service_id', 'reviewer_name',
                    'is_verified_client', 'is_anonymous', 'rating',
                    'communication_rating', 'clarity_rating', 'organization_rating',
                    'price_transparency_rating', 'body', 'status', 'expert_reply',
                    'replied_at', 'created_at',
                ])
                ->published()
                ->latest(),
            'forumAnswers' => fn ($answers) => $answers
                ->select([
                    'id', 'topic_id', 'expert_profile_id', 'body', 'sources',
                    'helpful_count', 'created_at', 'status',
                ])
                ->where('status', 'published')
                ->with([
                    'topic' => fn ($topics) => $topics->select(['id', 'slug', 'title', 'status', 'visibility']),
                ])
                ->latest()
                ->limit(4),
        ]);

        $engagement = ExpertEngagement::query()->firstOrCreate(
            ['expert_profile_id' => $profile->id, 'user_key' => $this->actor->key()],
            ['is_saved' => false, 'is_subscribed' => false, 'last_viewed_at' => now()],
        );
        $engagement->update(['last_viewed_at' => now()]);

        $reviewableBookings = Booking::query()
            ->select(['id', 'reference', 'expert_profile_id', 'service_id', 'pet_name', 'completed_at'])
            ->where('expert_profile_id', $profile->id)
            ->forClient($this->actor->key())
            ->where('status', BookingStatus::Completed->value)
            ->whereDoesntHave('review')
            ->with(['service' => fn ($services) => $services->select(['id', 'name'])])
            ->latest('completed_at')
            ->get();

        return [
            'page_title' => __('presentation.expert_profile_title', ['name' => $profile->public_name]),
            'active_section' => 'experts',
            'expert' => $this->detail($profile),
            'services' => $profile->services->map(fn (Service $service): array => $this->service($service))->all(),
            'slots' => $profile->availabilitySlots->map(fn (AvailabilitySlot $slot): array => $this->slot($slot))->all(),
            'credentials' => $profile->credentials->map(fn (Credential $credential): array => [
                'title' => $credential->title,
                'type' => $credential->credentialType()?->label() ?? Str::headline($credential->type),
                'issuer' => $credential->issuer,
                'region' => $credential->region,
                'masked_number' => $credential->number_last_four
                    ? __('presentation.ends_in', ['digits' => $credential->number_last_four])
                    : null,
                'status' => $credential->status->label(),
                'verified_at' => $this->formatter->monthYear($credential->verified_at),
                'expires_at' => $this->formatter->monthYear($credential->expires_at),
            ])->all(),
            'publications' => $profile->publications->map(fn (Publication $publication): array => [
                'title' => $publication->title,
                'summary' => $publication->summary,
                'type' => Str::headline($publication->type),
                'category' => Str::headline($publication->category),
                'reviewed' => $this->formatter->date($publication->last_reviewed_at),
                'sources' => $publication->sources ?? [],
                'conflict_disclosure' => $publication->conflict_disclosure,
            ])->all(),
            'reviews' => $profile->reviews->map(fn (Review $review): array => $this->review($review))->all(),
            'forum_answers' => $profile->forumAnswers
                ->filter(fn (ForumAnswer $answer): bool => $answer->topic !== null)
                ->map(fn (ForumAnswer $answer): array => [
                    'topic_slug' => $answer->topic->slug,
                    'topic_title' => $answer->topic->title,
                    'excerpt' => Str::limit($answer->body, 210),
                    'helpful_count' => $answer->helpful_count,
                    'created_label' => $this->formatter->relative($answer->created_at),
                ])
                ->values()
                ->all(),
            'engagement' => [
                'is_saved' => $engagement->is_saved,
                'is_subscribed' => $engagement->is_subscribed,
            ],
            'reviewable_bookings' => $reviewableBookings->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'label' => $booking->pet_name.' · '.$booking->service->name,
            ])->all(),
            'can_manage' => $profile->owner_key === $this->actor->key(),
        ];
    }

    /** @return array<string, mixed> */
    public function editor(?ExpertProfile $profile = null): array
    {
        return [
            'page_title' => $profile ? __('messages.edit_professional_profile') : __('messages.create_professional_profile'),
            'active_section' => 'experts',
            'expert' => $profile,
            'types' => $this->taxonomy->types(),
            'species_options' => $this->taxonomy->species(),
            'specializations' => $this->taxonomy->specializations(),
            'formats' => $this->taxonomy->formats(),
            'languages' => $this->taxonomy->languages(),
            'credential_types' => collect(CredentialType::cases())
                ->mapWithKeys(static fn (CredentialType $type): array => [
                    $type->value => $type->label(),
                ])
                ->all(),
            'age_groups' => [
                'newborn' => __('messages.newborn'),
                'young' => __('messages.young'),
                'adult' => __('messages.adult'),
                'senior' => __('messages.senior'),
                'special-needs' => __('messages.special_needs'),
            ],
            'accessibility_options' => [
                'ramp' => __('messages.ramp'),
                'lift' => __('messages.lift'),
                'accessible-toilet' => __('messages.accessible_toilet'),
                'quiet-zone' => __('messages.quiet_waiting_zone'),
                'parking' => __('messages.nearby_parking'),
                'wait-in-car' => __('messages.wait_in_car'),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function bookingForm(ExpertProfile $profile): array
    {
        $profile->load([
            'services' => fn ($services) => $services
                ->select([
                    'id', 'expert_profile_id', 'name', 'format', 'description',
                    'duration_minutes', 'price', 'currency', 'includes',
                    'preparation', 'cancellation_policy', 'requires_payment',
                    'requires_approval', 'status',
                ])
                ->active()
                ->orderBy('price'),
            'availabilitySlots' => fn ($slots) => $slots
                ->select([
                    'id', 'expert_profile_id', 'service_id', 'starts_at', 'ends_at',
                    'timezone', 'format', 'location_label', 'capacity',
                    'booked_count', 'status',
                ])
                ->open()
                ->orderBy('starts_at'),
        ]);
        $species = $this->taxonomy->species();

        return [
            'page_title' => __('messages.book').$profile->public_name,
            'active_section' => 'experts',
            'expert' => $this->detail($profile),
            'services' => $profile->services->map(fn (Service $service): array => $this->service($service))->all(),
            'slots' => $profile->availabilitySlots->map(fn (AvailabilitySlot $slot): array => $this->slot($slot))->all(),
            'pets' => collect($this->taxonomy->petData())
                ->map(static fn (array $pet): array => [
                    ...$pet,
                    'species_label' => $species[$pet['species']] ?? Str::headline($pet['species']),
                ])
                ->all(),
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    /** @return array<string, mixed> */
    public function booking(Booking $booking): array
    {
        $booking->load([
            'expertProfile' => fn ($profiles) => $profiles->forDirectory(),
            'service' => fn ($services) => $services->select([
                'id', 'expert_profile_id', 'name', 'format', 'description',
                'duration_minutes', 'price', 'currency', 'includes', 'preparation',
                'cancellation_policy',
            ]),
            'consultation' => fn ($consultations) => $consultations->select([
                'id', 'booking_id', 'expert_profile_id', 'status', 'started_at',
                'ended_at', 'client_summary', 'action_plan', 'referral_summary',
                'follow_up_until', 'summary_confirmed_at',
            ]),
            'documentGrants' => fn ($grants) => $grants->select([
                'id', 'booking_id', 'expert_profile_id', 'owner_key', 'label',
                'document_type', 'permissions', 'expires_at', 'last_opened_at',
                'downloaded_at', 'revoked_at', 'created_at',
            ]),
            'review' => fn ($reviews) => $reviews->select([
                'id', 'booking_id', 'rating', 'body', 'status',
            ]),
        ]);

        $audit = AuditLog::query()
            ->select(['id', 'booking_id', 'actor_role', 'action', 'metadata', 'created_at'])
            ->where('booking_id', $booking->id)
            ->oldest('created_at')
            ->get();

        return [
            'page_title' => __('messages.appointment').$booking->reference,
            'active_section' => 'experts',
            'booking' => [
                'reference' => $booking->reference,
                'status' => $booking->status->label(),
                'status_value' => $booking->status->value,
                'payment_status' => $booking->payment_status->label(),
                'pet_name' => $booking->pet_name,
                'pet_species' => Str::headline($booking->pet_species),
                'format' => Str::headline($booking->format),
                'starts_at' => $this->formatter->dateTime($booking->starts_at, $booking->timezone),
                'timezone' => $booking->timezone,
                'location' => $booking->location_label,
                'amount' => $booking->amount,
                'currency' => $booking->currency,
                'questionnaire' => $booking->questionnaire,
                'questionnaire_rows' => collect($booking->questionnaire)
                    ->reject(
                        static fn (mixed $value, string $key): bool => $key === 'urgent_signs'
                            || blank($value),
                    )
                    ->map(fn (mixed $value, string $key): array => [
                        'label' => Str::headline(str_replace('_', ' ', $key)),
                        'value' => is_bool($value)
                            ? ($value ? __('ui.yes') : __('ui.no'))
                            : (string) $value,
                    ])
                    ->values()
                    ->all(),
                'can_cancel' => ! in_array($booking->status, [BookingStatus::Cancelled, BookingStatus::Completed], true),
            ],
            'expert' => $this->detail($booking->expertProfile),
            'service' => $this->service($booking->service),
            'consultation' => $booking->consultation ? [
                'id' => $booking->consultation->id,
                'status' => $booking->consultation->status->label(),
                'client_summary' => $booking->consultation->client_summary,
                'action_plan' => $booking->consultation->action_plan ?? [],
                'referral_summary' => $booking->consultation->referral_summary,
                'follow_up_until' => $this->formatter->date($booking->consultation->follow_up_until),
                'is_confirmed' => $booking->consultation->summary_confirmed_at !== null,
            ] : null,
            'documents' => $booking->documentGrants->map(fn ($grant): array => [
                'id' => $grant->id,
                'label' => $grant->label,
                'type' => Str::headline($grant->document_type),
                'expires_at' => $this->formatter->dateTime($grant->expires_at),
                'revoked' => $grant->revoked_at !== null,
                'downloaded' => $grant->downloaded_at !== null,
            ])->all(),
            'audit' => $audit->map(fn (AuditLog $log): array => [
                'action' => Str::headline(str_replace('.', ' ', $log->action)),
                'role' => Str::headline($log->actor_role),
                'created_label' => $this->formatter->dateTime($log->created_at),
            ])->all(),
            'can_manage_expert' => $booking->expertProfile->owner_key === $this->actor->key(),
        ];
    }

    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        $profile = ExpertProfile::query()
            ->forDirectory()
            ->withCount('bookings')
            ->where('owner_key', $this->actor->key())
            ->latest('id')
            ->first();

        if ($profile === null) {
            return [
                'page_title' => __('messages.professional_workspace'),
                'active_section' => 'experts',
                'expert' => null,
                'bookings' => [],
                'credentials' => [],
                'services' => [],
                'metrics' => [],
            ];
        }

        $profile->load([
            'bookings' => fn ($bookings) => $bookings
                ->select([
                    'id', 'expert_profile_id', 'service_id', 'reference',
                    'client_name', 'pet_name', 'pet_species', 'format', 'starts_at',
                    'status', 'payment_status',
                ])
                ->with(['service' => fn ($services) => $services->select(['id', 'name'])])
                ->latest('starts_at')
                ->latest('id')
                ->limit(self::DASHBOARD_BOOKING_LIMIT),
            'credentials' => fn ($credentials) => $credentials->select([
                'id', 'expert_profile_id', 'type', 'title', 'issuer', 'expires_at',
                'status', 'verified_at',
            ])
                ->latest('id')
                ->limit(self::DASHBOARD_CREDENTIAL_LIMIT),
            'services' => fn ($services) => $services
                ->select([
                    'id', 'expert_profile_id', 'name', 'format', 'description',
                    'duration_minutes', 'price', 'currency', 'pricing_model',
                    'includes', 'excludes', 'preparation', 'cancellation_policy',
                    'follow_up_days', 'requires_payment', 'requires_approval',
                    'status',
                ])
                ->orderBy('name')
                ->orderBy('id')
                ->limit(self::DASHBOARD_SERVICE_LIMIT),
        ]);

        return [
            'page_title' => __('messages.professional_workspace'),
            'active_section' => 'experts',
            'expert' => $this->detail($profile),
            'bookings' => $profile->bookings->map(fn (Booking $booking): array => [
                'reference' => $booking->reference,
                'client_name' => $booking->client_name,
                'pet_name' => $booking->pet_name,
                'pet_species' => Str::headline($booking->pet_species),
                'service' => $booking->service->name,
                'format' => Str::headline($booking->format),
                'starts_at' => $this->formatter->dateTime($booking->starts_at),
                'status' => $booking->status->label(),
                'payment_status' => $booking->payment_status->label(),
            ])->all(),
            'credentials' => $profile->credentials->map(fn (Credential $credential): array => [
                'title' => $credential->title,
                'issuer' => $credential->issuer,
                'status' => $credential->status->label(),
                'expires_at' => $this->formatter->monthYear($credential->expires_at),
            ])->all(),
            'services' => $profile->services->map(fn (Service $service): array => $this->service($service))->all(),
            'metrics' => [
                ['label' => __('messages.profile_views'), 'value' => 0, 'note' => __('messages.private_viewer_identities_are_never_exposed')],
                ['label' => __('messages.bookings'), 'value' => (int) $profile->getAttribute('bookings_count'), 'note' => __('messages.current_workspace_data')],
                ['label' => __('messages.verified_reviews'), 'value' => $profile->verified_review_count, 'note' => __('messages.linked_to_completed_services')],
                ['label' => __('messages.forum_answers'), 'value' => $profile->forum_answer_count, 'note' => __('messages.professional_contributions')],
            ],
        ];
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'availability' => $query->orderBy('next_available_at')->orderBy('id'),
            'rating' => $query->orderByDesc('review_average')->orderByDesc('verified_review_count')->orderBy('id'),
            'experience' => $query->orderByDesc('years_experience')->orderBy('id'),
            'newest' => $query->latest('created_at')->latest('id'),
            default => $query
                ->orderByDesc('qualification_verified')
                ->orderByDesc('accepts_new_clients')
                ->orderByDesc('verified_review_count')
                ->orderBy('next_available_at')
                ->orderBy('id'),
        };
    }

    /** @return array<int, array{label: string, value: int, icon: string}> */
    private function stats(): array
    {
        $published = fn (): Builder => ExpertProfile::query()->published();

        return [
            ['label' => __('messages.published_profiles'), 'value' => $published()->count(), 'icon' => 'stethoscope'],
            ['label' => __('messages.qualifications_verified'), 'value' => $published()->where('qualification_verified', true)->count(), 'icon' => 'badge-check'],
            ['label' => __('messages.accepting_clients'), 'value' => $published()->where('accepts_new_clients', true)->count(), 'icon' => 'calendar-check'],
            ['label' => __('messages.species_covered'), 'value' => count($this->taxonomy->species()), 'icon' => 'paw-print'],
        ];
    }

    /** @param array<string, mixed> $filters */
    private function card(ExpertProfile $profile, array $filters = []): array
    {
        $profileUrl = route('experts.show', $profile->slug);
        $species = $this->taxonomy->species();
        $specializations = $this->taxonomy->specializations();
        $languages = $this->taxonomy->languages();
        $reasons = collect([
            filled($filters['species'] ?? null)
                ? __('presentation.works_with', [
                    'species' => $species[(string) $filters['species']] ?? Str::headline((string) $filters['species']),
                ])
                : null,
            filled($filters['specialization'] ?? null)
                ? __('presentation.specializes_in', [
                    'specialization' => $specializations[(string) $filters['specialization']]
                        ?? Str::headline((string) $filters['specialization']),
                ])
                : null,
            filled($filters['city'] ?? null)
                ? __('presentation.available_in', ['city' => $profile->city])
                : null,
            filled($filters['language'] ?? null)
                ? __('presentation.consults_in', [
                    'language' => $languages[(string) $filters['language']] ?? (string) $filters['language'],
                ])
                : null,
            $profile->qualification_verified ? __('messages.qualification_verified') : null,
        ])->filter()->take(3)->values()->all();

        return [
            'slug' => $profile->slug,
            'name' => $profile->public_name,
            'profile_url' => $profileUrl,
            'media_target' => [
                'url' => $profileUrl,
                'label' => __('presentation.open_profile', ['name' => $profile->public_name]),
            ],
            'initials' => collect(explode(' ', $profile->public_name))->take(2)->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))->join(''),
            'avatar_url' => $profile->avatar_url,
            'type' => $this->taxonomy->types()[$profile->primary_type] ?? Str::headline($profile->primary_type),
            'headline' => $profile->headline,
            'city' => $profile->city.', '.$profile->country,
            'specializations' => collect($profile->specializations)->map(fn (string $value): string => $specializations[$value] ?? Str::headline($value))->all(),
            'species' => collect($profile->species)->map(fn (string $value): string => $species[$value] ?? Str::headline($value))->all(),
            'languages' => collect($profile->languages)->map(fn (string $value): string => $languages[$value] ?? $value)->all(),
            'formats' => collect($profile->formats)->map(fn (string $value): string => $this->taxonomy->formats()[$value] ?? Str::headline($value))->all(),
            'verification' => $profile->verification_status->label(),
            'qualification_verified' => $profile->qualification_verified,
            'accepts_new_clients' => $profile->accepts_new_clients,
            'next_available' => $this->formatter->dateTime($profile->next_available_at),
            'price_from' => $profile->price_from,
            'currency' => $profile->currency,
            'rating' => $profile->review_average,
            'review_count' => $profile->review_count,
            'verified_review_count' => $profile->verified_review_count,
            'is_saved' => (bool) ($profile->is_saved ?? false),
            'reasons' => $reasons,
        ];
    }

    /** @return array<string, mixed> */
    private function detail(ExpertProfile $profile): array
    {
        return [
            ...$this->card($profile),
            'bio' => $profile->bio,
            'approach' => $profile->approach,
            'boundaries' => $profile->boundaries,
            'years_experience' => $profile->years_experience,
            'service_area' => $profile->service_area,
            'age_groups' => collect($profile->age_groups ?? [])->map(fn (string $value): string => Str::headline($value))->all(),
            'methods' => $profile->methods ?? [],
            'workplaces' => $profile->workplaces ?? [],
            'accessibility' => collect($profile->accessibility ?? [])->map(fn (string $value): string => Str::headline($value))->all(),
            'availability_status' => Str::headline($profile->availability_status),
            'response_time' => $profile->response_time,
            'offers_emergency_care' => $profile->offers_emergency_care,
            'profile_status' => $profile->status->label(),
            'verification_items' => [
                ['label' => __('messages.identity'), 'verified' => $profile->identity_verified, 'detail' => __('messages.government_identity_checked_privately')],
                ['label' => __('messages.education'), 'verified' => $profile->education_verified, 'detail' => __('messages.education_document_matched_to_the_stated_field')],
                ['label' => __('messages.qualification'), 'verified' => $profile->qualification_verified, 'detail' => __('messages.professional_scope_checked')],
                ['label' => __('messages.license'), 'verified' => $profile->license_verified, 'detail' => __('messages.current_license_checked_where_required')],
                ['label' => __('messages.workplace'), 'verified' => $profile->workplace_verified, 'detail' => __('messages.current_workplace_relationship_confirmed')],
                ['label' => __('messages.organization'), 'verified' => $profile->organization_verified, 'detail' => __('messages.organization_record_confirmed_separately')],
                ['label' => __('messages.contact'), 'verified' => $profile->contact_verified, 'detail' => __('messages.professional_contact_channel_confirmed')],
            ],
            'verification_expires' => $this->formatter->monthYear($profile->verification_expires_at),
        ];
    }

    /** @return array<string, mixed> */
    private function service(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'format' => Str::headline($service->format),
            'format_value' => $service->format,
            'description' => $service->description,
            'duration' => __('presentation.minutes', [
                'count' => $this->formatter->number($service->duration_minutes),
            ]),
            'price' => $service->price,
            'currency' => $service->currency,
            'pricing_model' => Str::headline($service->pricing_model ?? 'fixed'),
            'includes' => $service->includes ?? [],
            'excludes' => $service->excludes ?? [],
            'preparation' => $service->preparation ?? [],
            'cancellation_policy' => $service->cancellation_policy,
            'follow_up_days' => $service->follow_up_days ?? 0,
            'requires_payment' => $service->requires_payment ?? false,
            'requires_approval' => $service->requires_approval ?? false,
        ];
    }

    /** @return array<string, mixed> */
    private function slot(AvailabilitySlot $slot): array
    {
        return [
            'id' => $slot->id,
            'service_id' => $slot->service_id,
            'label' => $this->formatter->dateTime($slot->starts_at, $slot->timezone),
            'ends_at' => $this->formatter->time($slot->ends_at, $slot->timezone),
            'timezone' => $slot->timezone,
            'format' => Str::headline($slot->format),
            'location' => $slot->location_label,
            'remaining' => max(0, $slot->capacity - $slot->booked_count),
        ];
    }

    /** @return array<string, mixed> */
    private function review(Review $review): array
    {
        return [
            'reviewer_name' => $review->is_anonymous ? __('messages.verified_client') : $review->reviewer_name,
            'is_verified_client' => $review->is_verified_client,
            'rating' => $review->rating,
            'communication_rating' => $review->communication_rating,
            'clarity_rating' => $review->clarity_rating,
            'organization_rating' => $review->organization_rating,
            'price_transparency_rating' => $review->price_transparency_rating,
            'body' => $review->body,
            'expert_reply' => $review->expert_reply,
            'created_label' => $this->formatter->date($review->created_at),
        ];
    }
}
