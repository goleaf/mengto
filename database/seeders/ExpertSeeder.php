<?php

namespace Database\Seeders;

use App\Enums\ExpertProfileStatus;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\Credential;
use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\Publication;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LogicException;

class ExpertSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        $this->assertDemoSeedingIsAllowed();

        if (ExpertProfile::query()->where('slug', 'dr-emilia-vaitke')->exists()) {
            $this->assertExistingGraphIsComplete();

            return;
        }

        $avian = $this->profile([
            'owner_key' => 'dr-emilia',
            'slug' => 'dr-emilia-vaitke',
            'public_name' => 'Dr. Emilia Vaitke',
            'primary_type' => 'avian-veterinarian',
            'headline' => 'Avian veterinarian for parrots and companion birds',
            'bio' => 'Emilia works with parrots, small companion birds, and urgent avian concerns that need an in-person examination. Her profile separates scheduled consultations from emergency clinic coverage and shows the exact species scope.',
            'approach' => 'Calm handling, clear preparation instructions, and referral when imaging, surgery, or overnight care is needed.',
            'boundaries' => 'No diagnosis from a photo alone. Breathing difficulty, severe bleeding, seizures, collapse, or suspected poisoning require direct emergency contact.',
            'years_experience' => 11,
            'specializations' => ['avian-medicine', 'general-practice'],
            'species' => ['bird', 'parrot'],
            'formats' => ['in-person', 'video', 'asynchronous'],
            'languages' => ['Lithuanian', 'English', 'Russian'],
            'price_from' => 58,
            'next_available_at' => now()->addHours(5),
            'avatar_url' => asset('images/places/veterinary-primary-md.jpg'),
        ]);
        $avianService = $this->service($avian, [
            'slug' => 'avian-clinic-visit',
            'name' => 'Avian clinic visit',
            'type' => 'initial-consultation',
            'format' => 'in-person',
            'description' => 'A scheduled avian examination with history review and an agreed diagnostic next step.',
            'duration_minutes' => 50,
            'price' => 68,
            'includes' => ['Clinical consultation', 'Written visit summary'],
            'excludes' => ['Laboratory tests', 'Imaging', 'Medication'],
            'preparation' => ['Bring the bird in a secure carrier', 'Bring medication and diet details'],
        ]);
        $this->slots($avian, $avianService, 9, 'Paws 24 · Žvėrynas entrance');

        $trainer = $this->profile([
            'owner_key' => 'eva-jonas',
            'slug' => 'eva-jonas',
            'public_name' => 'Eva Jonas',
            'primary_type' => 'dog-trainer',
            'headline' => 'Low-pressure dog trainer for fear and urban confidence',
            'bio' => 'Eva supports dogs who struggle with lifts, traffic, other dogs, and unfamiliar environments. Sessions start below the dog’s stress threshold and include a practical plan for the owner.',
            'approach' => 'Reward-based observation, careful distance, short sessions, and measurable recovery rather than forced exposure.',
            'boundaries' => 'Does not use painful tools or guarantee a fixed result. Sudden behavior change or suspected pain is referred for veterinary assessment.',
            'years_experience' => 9,
            'specializations' => ['behavior', 'training'],
            'species' => ['dog'],
            'formats' => ['in-person', 'video'],
            'methods' => ['Reward-based training', 'Choice and distance', 'Owner coaching'],
            'price_from' => 49,
            'next_available_at' => now()->addDay(),
            'avatar_url' => asset('images/places/veterinary-secondary-md.jpg'),
        ]);
        $trainerService = $this->service($trainer, [
            'slug' => 'behavior-walk',
            'name' => 'Individual behavior walk',
            'type' => 'individual-session',
            'format' => 'in-person',
            'description' => 'A quiet first session in a suitable public place with an owner practice plan.',
            'duration_minutes' => 60,
            'price' => 55,
            'includes' => ['Initial assessment', 'Practice plan', 'One follow-up question'],
            'excludes' => ['Veterinary assessment'],
            'preparation' => ['Use familiar walking equipment', 'Send a short video from a safe distance'],
            'follow_up_days' => 7,
        ]);
        $this->slots($trainer, $trainerService, 10, 'Quiet public meeting point');

        $catBehavior = $this->profile([
            'owner_key' => 'sofia-behavior',
            'slug' => 'sofia-arden',
            'public_name' => 'Sofia Arden',
            'primary_type' => 'cat-behavior-consultant',
            'headline' => 'Feline behavior consultant for carriers, litter boxes, and introductions',
            'bio' => 'Sofia works with home environment, gradual carrier training, multi-cat introductions, and relocation stress. Her verified scope is feline behavior, not veterinary medicine.',
            'approach' => 'Choice-based routines, environmental enrichment, and realistic progress measures for the household.',
            'boundaries' => 'Does not diagnose disease or prescribe medication. Pain, urinary changes, appetite loss, and sudden behavior change are referred to a veterinarian.',
            'years_experience' => 8,
            'specializations' => ['behavior', 'feline-care'],
            'species' => ['cat'],
            'formats' => ['video', 'text', 'asynchronous'],
            'price_from' => 44,
            'next_available_at' => now()->addDays(2),
            'avatar_url' => asset('images/places/veterinary-tertiary-md.jpg'),
        ]);
        $catService = $this->service($catBehavior, [
            'slug' => 'feline-video-consultation',
            'name' => 'Feline video consultation',
            'type' => 'video-consultation',
            'format' => 'video',
            'description' => 'A structured video consultation with environment review and a written step-by-step plan.',
            'duration_minutes' => 50,
            'price' => 49,
            'includes' => ['Video call', 'Written plan', 'Seven days for one follow-up question'],
            'excludes' => ['Veterinary diagnosis', 'Medication advice'],
            'preparation' => ['Send a room overview', 'Describe the daily routine'],
            'follow_up_days' => 7,
        ]);
        $catSlot = $this->slots($catBehavior, $catService, 11, 'Secure video room')->first();

        $groomer = $this->profile([
            'owner_key' => 'quiet-whiskers',
            'slug' => 'irena-petrauske',
            'public_name' => 'Irena Petrauskė',
            'primary_type' => 'cat-groomer',
            'headline' => 'Quiet individual grooming for cats and senior pets',
            'bio' => 'Irena offers one-pet appointments without dogs in the room, pauses when stress rises, and discusses what can be completed safely before starting.',
            'approach' => 'Short handling steps, quiet equipment, and permission to stop the procedure.',
            'boundaries' => 'Does not treat skin disease or sedate animals. Pain, wounds, or severe matting that needs medical support is referred to a veterinarian.',
            'years_experience' => 7,
            'specializations' => ['grooming', 'feline-care', 'senior-care'],
            'species' => ['cat', 'dog'],
            'formats' => ['in-person'],
            'accessibility' => ['parking', 'quiet-zone', 'wait-in-car'],
            'price_from' => 38,
            'next_available_at' => now()->addDays(3),
            'avatar_url' => asset('images/places/grooming-primary-md.jpg'),
        ]);
        $grooming = $this->service($groomer, [
            'slug' => 'quiet-cat-grooming',
            'name' => 'Quiet cat grooming',
            'type' => 'grooming',
            'format' => 'in-person',
            'description' => 'An individual cat appointment with a stress-aware stop rule.',
            'duration_minutes' => 70,
            'price' => 52,
            'includes' => ['Coat assessment', 'Agreed grooming steps'],
            'excludes' => ['Sedation', 'Treatment of skin disease'],
            'preparation' => ['Describe previous grooming incidents', 'Bring the familiar carrier'],
        ]);
        $this->slots($groomer, $grooming, 12, 'Quiet Whiskers Studio');

        $rehab = $this->profile([
            'owner_key' => 'jonas-rehab',
            'slug' => 'jonas-kairys',
            'public_name' => 'Jonas Kairys',
            'primary_type' => 'physiotherapist',
            'headline' => 'Animal rehabilitation and mobility support',
            'bio' => 'Jonas supports post-operative recovery, senior mobility, and home exercise plans under the appropriate veterinary context.',
            'approach' => 'Small measurable goals, owner demonstrations, and regular reassessment.',
            'boundaries' => 'A veterinary referral is required for post-operative and unexplained pain cases. Exercises stop if pain or neurological signs worsen.',
            'years_experience' => 10,
            'specializations' => ['rehabilitation', 'senior-care'],
            'species' => ['dog', 'cat'],
            'formats' => ['in-person', 'video'],
            'price_from' => 50,
            'next_available_at' => now()->addDays(4),
            'avatar_url' => asset('images/places/community-primary-md.jpg'),
        ]);
        $rehabService = $this->service($rehab, [
            'slug' => 'mobility-assessment',
            'name' => 'Mobility assessment',
            'type' => 'rehabilitation',
            'format' => 'in-person',
            'description' => 'A mobility assessment and a veterinarian-aligned home exercise plan.',
            'duration_minutes' => 55,
            'price' => 62,
            'includes' => ['Assessment', 'Home plan', 'Progress criteria'],
            'excludes' => ['Veterinary diagnosis'],
            'preparation' => ['Bring referral or discharge summary', 'Bring short walking videos'],
        ]);
        $this->slots($rehab, $rehabService, 13, 'Mobility Center Vilnius');

        $feline = $this->profile([
            'owner_key' => 'laura-feline',
            'slug' => 'laura-zukauskaite',
            'public_name' => 'Laura Žukauskaitė',
            'primary_type' => 'feline-specialist',
            'headline' => 'Feline specialist for breed care and show preparation',
            'bio' => 'Laura advises on breed standards, coat preparation, show routines, and responsible club documentation.',
            'approach' => 'Clear separation of breed knowledge, grooming, behavior, and veterinary care.',
            'boundaries' => 'Not a veterinarian and does not diagnose disease or prescribe treatment.',
            'years_experience' => 12,
            'specializations' => ['feline-care'],
            'species' => ['cat'],
            'formats' => ['video', 'in-person'],
            'price_from' => 35,
            'next_available_at' => now()->addDays(5),
        ]);
        $felineService = $this->service($feline, [
            'slug' => 'show-preparation-review',
            'name' => 'Show preparation review',
            'type' => 'feline-consultation',
            'format' => 'video',
            'description' => 'A breed-aware preparation review without medical claims.',
            'duration_minutes' => 40,
            'price' => 38,
        ]);
        $this->slots($feline, $felineService, 14, 'Secure video room');

        $shelter = $this->profile([
            'owner_key' => 'vilnius-aid',
            'slug' => 'ana-petraityte',
            'public_name' => 'Ana Petraitytė',
            'primary_type' => 'shelter-specialist',
            'headline' => 'Shelter adoption and volunteer support coordinator',
            'bio' => 'Ana helps families prepare for adoption, understand the first weeks, and coordinate verified shelter volunteering.',
            'approach' => 'Practical preparation, honest compatibility discussion, and follow-up with the shelter.',
            'boundaries' => 'Does not provide veterinary diagnoses or guarantee that an adoption match will work.',
            'years_experience' => 8,
            'specializations' => ['adoption', 'behavior'],
            'species' => ['dog', 'cat'],
            'formats' => ['video', 'text', 'in-person'],
            'price_from' => 0,
            'next_available_at' => now()->addDays(2),
        ]);
        $shelterService = $this->service($shelter, [
            'slug' => 'adoption-preparation',
            'name' => 'Adoption preparation call',
            'type' => 'adoption-support',
            'format' => 'video',
            'description' => 'A free preparation call linked to the shelter adoption process.',
            'duration_minutes' => 30,
            'price' => 0,
            'includes' => ['Home preparation checklist'],
            'excludes' => ['Medical assessment'],
        ]);
        $this->slots($shelter, $shelterService, 15, 'Secure video room');

        $mia = ExpertProfile::factory()->unverified()->create([
            'owner_id' => User::query()->where('actor_key', 'mia-carter')->valueOrFail('id'),
            'owner_key' => 'mia-carter',
            'slug' => 'mia-carter-care-coordinator',
            'public_name' => 'Mia Carter',
            'primary_type' => 'shelter-specialist',
            'headline' => 'Volunteer care coordinator profile in review',
            'bio' => 'Mia is preparing a professional volunteer coordination profile. It remains outside public search until the submitted role and organization relationship are reviewed.',
            'approach' => 'Clear task ownership and privacy-aware volunteer coordination.',
            'boundaries' => 'No medical advice, diagnosis, medication changes, or emergency intake.',
            'specializations' => ['adoption'],
            'species' => ['dog', 'cat'],
            'formats' => ['text', 'video'],
            'status' => ExpertProfileStatus::Pending,
            'verification_status' => VerificationStatus::Submitted,
            'accepts_new_clients' => false,
            'next_available_at' => null,
            'price_from' => null,
            'review_average' => 0,
            'review_count' => 0,
            'verified_review_count' => 0,
        ]);
        Credential::factory()->create([
            'expert_profile_id' => $mia->id,
            'type' => 'organization-role',
            'title' => 'Volunteer coordinator confirmation',
            'issuer' => 'Vilnius Animal Aid',
            'status' => 'submitted',
            'verified_at' => null,
            'expires_at' => null,
        ]);

        $booking = Booking::factory()->completed()->create([
            'client_id' => User::query()->where('actor_key', 'mia-carter')->valueOrFail('id'),
            'expert_profile_id' => $catBehavior->id,
            'service_id' => $catService->id,
            'availability_slot_id' => $catSlot->id,
            'reference' => (string) Str::uuid(),
            'idempotency_key' => (string) Str::uuid(),
            'pet_key' => 'nori',
            'pet_name' => 'Nori',
            'pet_species' => 'cat',
            'pet_age_label' => '2 years',
            'format' => 'video',
            'starts_at' => now()->subWeeks(3),
            'ends_at' => now()->subWeeks(3)->addMinutes(50),
            'location_label' => 'Secure video room',
            'amount' => 49,
            'payment_status' => PaymentStatus::Paid,
        ]);
        Consultation::factory()->create([
            'booking_id' => $booking->id,
            'expert_profile_id' => $catBehavior->id,
            'status' => 'completed',
            'ended_at' => $booking->ends_at,
            'client_summary' => 'Keep the carrier open in the living area, reward voluntary investigation, and add the door only after relaxed entry is reliable.',
            'action_plan' => ['Leave the carrier open daily', 'Reward calm approaches', 'Stop before Nori tries to leave'],
            'follow_up_until' => now()->subWeeks(2),
            'summary_confirmed_at' => $booking->ends_at,
        ]);
        Review::factory()->create([
            'expert_profile_id' => $catBehavior->id,
            'service_id' => $catService->id,
            'booking_id' => $booking->id,
            'reviewer_key' => 'mia-carter',
            'reviewer_name' => 'Mia Carter',
            'body' => 'The scope was clear, the plan was practical, and Sofia explicitly separated behavior guidance from veterinary care.',
        ]);
        ExpertEngagement::factory()->create([
            'expert_profile_id' => $avian->id,
            'user_key' => 'mia-carter',
            'is_saved' => true,
            'is_subscribed' => true,
        ]);

        Publication::factory()->create([
            'expert_profile_id' => $avian->id,
            'slug' => 'prepare-a-parrot-for-an-avian-appointment',
            'title' => 'Prepare a parrot for an avian appointment',
            'summary' => 'A practical carrier, history, diet, and medication checklist before a scheduled bird consultation.',
            'category' => 'avian-medicine',
            'tags' => ['bird', 'parrot', 'clinic'],
            'conflict_disclosure' => 'No commercial relationship.',
        ]);
        Publication::factory()->create([
            'expert_profile_id' => $trainer->id,
            'slug' => 'work-below-a-dogs-fear-threshold',
            'title' => 'Work below a dog’s fear threshold',
            'summary' => 'How distance, recovery, and short sessions support a safer behavior plan.',
            'category' => 'behavior',
            'tags' => ['dog', 'fear', 'training'],
            'conflict_disclosure' => 'Author offers behavior consultations; the article includes a complete free safety framework.',
        ]);

        ForumAnswer::query()
            ->where('author_key', 'dr-emilia')
            ->update(['expert_profile_id' => $avian->id]);
        ForumAnswer::query()
            ->where('author_key', 'eva-jonas')
            ->update(['expert_profile_id' => $trainer->id]);
        ForumAnswer::query()
            ->where('author_key', 'sofia-behavior')
            ->update(['expert_profile_id' => $catBehavior->id]);

        $this->refreshMetrics($avian);
        $this->refreshMetrics($trainer);
        $this->refreshMetrics($catBehavior);
    }

    private function assertExistingGraphIsComplete(): void
    {
        $credentialTitles = [
            'dr-emilia-vaitke' => 'Avian Veterinarian qualification',
            'eva-jonas' => 'Dog Trainer qualification',
            'sofia-arden' => 'Cat Behavior Consultant qualification',
            'irena-petrauske' => 'Cat Groomer qualification',
            'jonas-kairys' => 'Physiotherapist qualification',
            'laura-zukauskaite' => 'Feline Specialist qualification',
            'ana-petraityte' => 'Shelter Specialist qualification',
            'mia-carter-care-coordinator' => 'Volunteer coordinator confirmation',
        ];
        $serviceSlugs = [
            'avian-clinic-visit',
            'behavior-walk',
            'feline-video-consultation',
            'quiet-cat-grooming',
            'mobility-assessment',
            'show-preparation-review',
            'adoption-preparation',
        ];

        $profiles = ExpertProfile::query()
            ->whereIn('slug', array_keys($credentialTitles))
            ->get()
            ->keyBy('slug');
        $services = Service::query()
            ->withCount('availabilitySlots')
            ->whereIn('slug', $serviceSlugs)
            ->get()
            ->keyBy('slug');

        $credentialsComplete = collect($credentialTitles)->every(
            static fn (string $title, string $slug): bool => isset($profiles[$slug])
                && Credential::query()
                    ->where('expert_profile_id', $profiles[$slug]->id)
                    ->where('title', $title)
                    ->exists(),
        );
        $slotsComplete = $services->count() === count($serviceSlugs)
            && $services->every(
                static fn (Service $service): bool => $service->availability_slots_count >= 3,
            );
        $catProfile = $profiles->get('sofia-arden');
        $catService = $services->get('feline-video-consultation');
        $bookingComplete = $catProfile instanceof ExpertProfile
            && $catService instanceof Service
            && Booking::query()
                ->where('expert_profile_id', $catProfile->id)
                ->where('service_id', $catService->id)
                ->where('client_key', 'mia-carter')
                ->whereHas('consultation')
                ->whereHas('review')
                ->exists();
        $publicationsComplete = Publication::query()
            ->whereIn('slug', [
                'prepare-a-parrot-for-an-avian-appointment',
                'work-below-a-dogs-fear-threshold',
            ])
            ->count() === 2;
        $engagementComplete = isset($profiles['dr-emilia-vaitke'])
            && ExpertEngagement::query()
                ->where('expert_profile_id', $profiles['dr-emilia-vaitke']->id)
                ->where('user_key', 'mia-carter')
                ->exists();
        $answerProfilesComplete = collect([
            'dr-emilia' => 'dr-emilia-vaitke',
            'eva-jonas' => 'eva-jonas',
            'sofia-behavior' => 'sofia-arden',
        ])->every(
            static fn (string $slug, string $authorKey): bool => isset($profiles[$slug])
                && ForumAnswer::query()
                    ->where('author_key', $authorKey)
                    ->where('expert_profile_id', $profiles[$slug]->id)
                    ->exists(),
        );

        if ($profiles->count() !== count($credentialTitles)
            || ! $credentialsComplete
            || ! $slotsComplete
            || ! $bookingComplete
            || ! $publicationsComplete
            || ! $engagementComplete
            || ! $answerProfilesComplete) {
            throw new LogicException('The deterministic expert demo graph is partially present.');
        }
    }

    /** @param array<string, mixed> $overrides */
    private function profile(array $overrides): ExpertProfile
    {
        $ownerKey = $overrides['owner_key'] ?? null;
        $ownerId = is_string($ownerKey)
            ? User::query()->where('actor_key', $ownerKey)->value('id')
            : null;
        $profile = ExpertProfile::factory()->create([
            'owner_id' => $ownerId,
            ...$overrides,
        ]);

        Credential::factory()->create([
            'expert_profile_id' => $profile->id,
            'title' => Str::headline($profile->primary_type).' qualification',
            'region' => $profile->country,
        ]);

        return $profile;
    }

    /** @param array<string, mixed> $overrides */
    private function service(ExpertProfile $profile, array $overrides): Service
    {
        return Service::factory()->create([
            'expert_profile_id' => $profile->id,
            ...$overrides,
        ]);
    }

    private function slots(
        ExpertProfile $profile,
        Service $service,
        int $hour,
        string $location,
    ): Collection {
        return collect([1, 3, 6])->map(fn (int $days): AvailabilitySlot => AvailabilitySlot::factory()->create([
            'expert_profile_id' => $profile->id,
            'service_id' => $service->id,
            'starts_at' => now()->addDays($days)->setTime($hour, 0),
            'ends_at' => now()->addDays($days)->setTime($hour, 0)->addMinutes($service->duration_minutes),
            'format' => $service->format,
            'location_label' => $location,
        ]));
    }

    private function refreshMetrics(ExpertProfile $profile): void
    {
        $reviews = Review::query()
            ->where('expert_profile_id', $profile->id)
            ->published();

        $profile->update([
            'forum_answer_count' => ForumAnswer::query()
                ->where('expert_profile_id', $profile->id)
                ->count(),
            'publication_count' => Publication::query()
                ->where('expert_profile_id', $profile->id)
                ->published()
                ->count(),
            'review_count' => (clone $reviews)->count(),
            'verified_review_count' => (clone $reviews)
                ->where('is_verified_client', true)
                ->count(),
            'review_average' => (float) ((clone $reviews)->avg('rating') ?? 0),
        ]);
    }
}
