<?php

use App\Enums\ExpertProfileStatus;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use App\Models\Service;

test('expert directory applies species and qualification filters without publishing pending profiles', function () {
    $avian = ExpertProfile::factory()->create([
        'public_name' => 'Dr. Emilia Vaitke',
        'slug' => 'dr-emilia-vaitke',
        'primary_type' => 'avian-veterinarian',
        'species' => ['bird', 'parrot'],
        'specializations' => ['avian-medicine'],
        'city' => 'Vilnius',
    ]);
    Service::factory()->create([
        'expert_profile_id' => $avian->id,
        'name' => 'Avian clinic visit',
    ]);

    ExpertProfile::factory()->create([
        'public_name' => 'General Dog Professional',
        'species' => ['dog'],
        'specializations' => ['training'],
    ]);

    ExpertProfile::factory()->unverified()->create([
        'public_name' => 'Pending Bird Profile',
        'species' => ['bird'],
        'status' => ExpertProfileStatus::Pending,
    ]);

    $this->get(route('experts.index', [
        'species' => 'bird',
        'specialization' => 'avian-medicine',
        'verified' => 1,
    ]))
        ->assertOk()
        ->assertSee('Find the right specialist for this pet')
        ->assertSee('Dr. Emilia Vaitke')
        ->assertSee('Qualification verified')
        ->assertDontSee('General Dog Professional')
        ->assertDontSee('Pending Bird Profile');
});

test('expert page explains independent checks services and exact forum expertise', function () {
    $expert = ExpertProfile::factory()->create([
        'public_name' => 'Sofia Arden',
        'slug' => 'sofia-arden',
        'primary_type' => 'cat-behavior-consultant',
        'species' => ['cat'],
        'specializations' => ['behavior', 'feline-care'],
        'license_verified' => false,
        'organization_verified' => false,
        'boundaries' => 'Does not diagnose disease or prescribe medication. Sudden changes require veterinary assessment.',
    ]);
    Credential::factory()->create([
        'expert_profile_id' => $expert->id,
        'title' => 'Feline behavior qualification',
        'number_last_four' => '4821',
    ]);
    Service::factory()->create([
        'expert_profile_id' => $expert->id,
        'name' => 'Feline video consultation',
        'format' => 'video',
        'price' => 49,
    ]);
    $topic = ForumTopic::factory()->create([
        'title' => 'Helping a cat feel safe around a carrier',
    ]);
    ForumAnswer::factory()->expert()->create([
        'topic_id' => $topic->id,
        'expert_profile_id' => $expert->id,
        'author_key' => $expert->owner_key,
        'author_name' => $expert->public_name,
    ]);

    $this->get(route('experts.show', $expert))
        ->assertOk()
        ->assertSee('What was checked')
        ->assertSee('License · not verified')
        ->assertSee('Organization · not verified')
        ->assertSee('Feline video consultation')
        ->assertSee('Ends in 4821')
        ->assertSee('Helping a cat feel safe around a carrier')
        ->assertSee('Does not diagnose disease');

    $this->get(route('forum.topics.show', $topic))
        ->assertOk()
        ->assertSee('Qualification verified')
        ->assertSee(route('experts.show', $expert), false);
});

test('newly verified professionals remain discoverable without client reviews', function () {
    ExpertProfile::factory()->create([
        'public_name' => 'New Verified Rehabilitation Professional',
        'primary_type' => 'physiotherapist',
        'review_count' => 0,
        'verified_review_count' => 0,
        'review_average' => 0,
    ]);

    $this->get(route('experts.index', ['sort' => 'newest']))
        ->assertOk()
        ->assertSee('New Verified Rehabilitation Professional')
        ->assertSee('New profile');
});

test('professional workspace shows the current specialists scope services and verification', function () {
    $expert = ExpertProfile::factory()->create([
        'owner_key' => 'mia-carter',
        'public_name' => 'Mia Care Professional',
        'primary_type' => 'shelter-specialist',
        'qualification_verified' => true,
        'license_verified' => false,
    ]);
    Service::factory()->create([
        'expert_profile_id' => $expert->id,
        'name' => 'Adoption preparation call',
    ]);

    $this->get(route('experts.dashboard'))
        ->assertOk()
        ->assertSee('Professional workspace')
        ->assertSee('Mia Care Professional')
        ->assertSee('Adoption preparation call')
        ->assertSee('Qualification')
        ->assertSee('License');
});
