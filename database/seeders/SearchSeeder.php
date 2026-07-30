<?php

namespace Database\Seeders;

use App\Enums\SearchStatus;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchSector;
use App\Models\SearchTask;
use App\Models\SearchUpdate;
use App\Models\SearchVolunteer;
use App\Models\Sighting;
use Illuminate\Database\Seeder;

class SearchSeeder extends Seeder
{
    public function run(): void
    {
        $scout = SearchCase::query()
            ->where('slug', 'scout-missing-vingis-park')
            ->first();

        if ($scout === null) {
            $scout = SearchCase::factory()->create([
                'owner_key' => 'mia-carter',
                'owner_name' => 'Mia Carter',
                'owner_initials' => 'MC',
                'coordinator_key' => 'mia-carter',
                'coordinator_name' => 'Mia Carter',
                'slug' => 'scout-missing-vingis-park',
                'public_code' => 'LF-SCOUT26',
                'active_key' => 'mia-carter:scout',
                'pet_profile_key' => 'scout',
                'pet_name' => 'Scout',
                'breed' => 'Labrador mix',
                'size' => 'large',
                'primary_color' => 'Black with a white chest',
                'distinctive_marks' => 'White chest patch, one folded ear, blue collar.',
                'hidden_marks' => 'Small crescent scar under the left shoulder.',
                'description' => 'Scout slipped out of his harness after a loud sound. He may keep distance even from familiar people while frightened.',
                'health_notice' => 'Needs a regular medication; contact the owner promptly if secured.',
                'last_seen_area' => 'Vingis Park, western river path',
                'public_latitude' => 54.683000,
                'public_longitude' => 25.238000,
                'exact_location' => [
                    'latitude' => 54.682941,
                    'longitude' => 25.237611,
                    'note' => 'Bench beside the southern gate',
                ],
                'last_seen_at' => now()->subHours(4),
                'reported_at' => now()->subHours(4),
                'latest_update' => 'A confirmed sighting moved the quiet search east toward the river.',
                'last_sighting_at' => now()->subMinutes(45),
            ]);
        }

        if (! $scout->sightings()->exists()) {
            Sighting::factory()->confirmed()->create([
                'search_case_id' => $scout->id,
                'reporter_key' => 'ari-jensen',
                'reporter_name' => 'Ari Jensen',
                'public_area' => 'Vingis Park east river path',
                'public_latitude' => 54.684000,
                'public_longitude' => 25.244000,
                'exact_location' => [
                    'latitude' => 54.683812,
                    'longitude' => 25.243774,
                    'note' => 'Near the quiet gravel junction',
                ],
                'observed_at' => now()->subMinutes(45),
                'notes' => 'Scout kept distance and moved east. No one followed.',
            ]);
            Sighting::factory()->create([
                'search_case_id' => $scout->id,
                'public_area' => 'Žvėrynas footbridge approach',
                'observed_at' => now()->subMinutes(20),
                'confidence' => 'possible',
                'notes' => 'Black dog seen briefly between trees; photo needs review.',
            ]);
        }

        if (! $scout->sectors()->exists()) {
            $river = SearchSector::factory()->create([
                'search_case_id' => $scout->id,
                'code' => 'A1',
                'label' => 'East river path',
                'priority' => 1,
                'risk_notes' => 'Busy cycle crossing near the northern edge.',
            ]);
            $entrances = SearchSector::factory()->create([
                'search_case_id' => $scout->id,
                'code' => 'B1',
                'label' => 'Western entrances',
                'priority' => 2,
            ]);

            SearchTask::factory()->create([
                'search_case_id' => $scout->id,
                'search_sector_id' => $river->id,
                'title' => 'Quietly check the east river path',
                'description' => 'Work in pairs, remain on public paths, and report locations without approaching.',
                'safety_level' => 'pair-required',
            ]);
            SearchTask::factory()->create([
                'search_case_id' => $scout->id,
                'search_sector_id' => $entrances->id,
                'type' => 'posters',
                'title' => 'Place QR posters at approved entrances',
                'description' => 'Ask permission and record each poster location for removal.',
            ]);
        }

        if (! $scout->volunteers()->exists()) {
            SearchVolunteer::factory()->create([
                'search_case_id' => $scout->id,
                'actor_key' => 'ari-jensen',
                'display_name' => 'Ari Jensen',
                'capabilities' => ['walking-search', 'transport'],
            ]);
            SearchVolunteer::factory()->create([
                'search_case_id' => $scout->id,
                'actor_key' => 'noah-williams',
                'display_name' => 'Noah Williams',
                'capabilities' => ['posters', 'phone-calls'],
            ]);
        }

        if (! $scout->updates()->where('type', 'sighting-confirmed')->exists()) {
            SearchUpdate::factory()->create([
                'search_case_id' => $scout->id,
                'type' => 'sighting-confirmed',
                'title' => 'New confirmed sighting',
                'body' => 'The priority zone moved east. Do not chase or call loudly.',
                'public_area' => 'Vingis Park east river path',
                'occurred_at' => now()->subMinutes(45),
            ]);
        }

        if (! $scout->alerts()->exists()) {
            SearchAlert::factory()->create([
                'search_case_id' => $scout->id,
                'region' => 'Vingis Park, Vilnius',
                'recipient_count' => 684,
            ]);
        }

        if (! SearchCase::query()->where('slug', 'found-tabby-naujamiestis')->exists()) {
            SearchCase::factory()->found()->create([
                'owner_key' => 'ari-jensen',
                'owner_name' => 'Ari Jensen',
                'owner_initials' => 'AJ',
                'coordinator_key' => 'ari-jensen',
                'coordinator_name' => 'Ari Jensen',
                'slug' => 'found-tabby-naujamiestis',
                'public_code' => 'LF-TABBY26',
                'primary_color' => 'Brown tabby with white paws',
                'distinctive_marks' => 'White front paws and a green collar without a tag.',
                'last_seen_area' => 'Naujamiestis, near the public library',
                'public_latitude' => 54.677000,
                'public_longitude' => 25.269000,
                'exact_location' => [
                    'latitude' => 54.677218,
                    'longitude' => 25.269411,
                    'note' => 'Temporary safe room; address withheld',
                ],
                'cover_url' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1400&q=85',
                'latest_update' => 'The cat is safe and will be scanned for a microchip.',
            ]);
        }

        if (! SearchCase::query()->where('slug', 'kesha-long-term-search')->exists()) {
            SearchCase::factory()->create([
                'owner_key' => 'noah-williams',
                'owner_name' => 'Noah Williams',
                'owner_initials' => 'NW',
                'coordinator_key' => 'noah-williams',
                'coordinator_name' => 'Noah Williams',
                'slug' => 'kesha-long-term-search',
                'public_code' => 'LF-KESHA26',
                'active_key' => 'noah-williams:kesha',
                'pet_profile_key' => 'kesha',
                'pet_name' => 'Kesha',
                'species' => 'bird',
                'breed' => 'Budgerigar',
                'sex' => 'male',
                'size' => 'very-small',
                'primary_color' => 'Green and yellow',
                'description' => 'Kesha flew from a balcony during strong wind. New verified matches are still welcome.',
                'status' => SearchStatus::LongTerm,
                'alerts_active' => false,
                'volunteer_join_open' => false,
                'last_seen_area' => 'Žirmūnai, Vilnius',
                'cover_url' => 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?auto=format&fit=crop&w=1400&q=85',
                'latest_update' => 'Urgent push alerts are paused; shelters can still submit possible matches.',
            ]);
        }
    }
}
