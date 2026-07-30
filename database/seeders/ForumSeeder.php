<?php

namespace Database\Seeders;

use App\Enums\ForumSubscriptionLevel;
use App\Enums\ForumTopicType;
use App\Models\ForumAnswer;
use App\Models\ForumComment;
use App\Models\ForumEngagement;
use App\Models\ForumNotification;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeVersion;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        if (ForumTopic::query()->exists()) {
            return;
        }

        $elevator = ForumTopic::factory()->create([
            'author_key' => 'mia-carter',
            'author_name' => 'Mia Carter',
            'author_initials' => 'MC',
            'author_role' => 'Scout and Nori owner',
            'slug' => 'calm-lift-entry-after-loud-noise',
            'type' => ForumTopicType::Question,
            'title' => 'How can I help my dog enter the lift calmly after a loud noise?',
            'body' => "Scout used the lift comfortably until a metal cart fell nearby last week. Now he stops several metres away, lowers his body, and will not take food near the doors.\n\nWe have paused at a distance where he can still look around and leave. I want a low-pressure plan and signs that mean we should involve a professional.",
            'category' => 'behavior',
            'subcategory' => 'fear',
            'tags' => ['fear', 'confidence', 'dog', 'lift'],
            'location' => 'Vilnius',
            'desired_answer' => 'professional-opinion',
            'has_expert_answer' => true,
            'view_count' => 348,
            'last_activity_at' => now()->subMinutes(18),
            'media' => [[
                'type' => 'image',
                'path' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=1400&q=85',
                'alt' => 'Dog waiting calmly near a building entrance',
                'sensitive' => false,
            ]],
        ]);

        $elevatorExpert = ForumAnswer::factory()->expert()->create([
            'topic_id' => $elevator->id,
            'author_key' => 'eva-jonas',
            'author_name' => 'Eva Jonas',
            'author_initials' => 'EJ',
            'author_role' => 'Verified behavior consultant',
            'expertise' => 'Fear, cooperative care, and low-pressure training',
            'body' => "Start outside the point where Scout freezes. Pair a glance toward the lift with something he values, then move away before tension rises. Keep sessions under two minutes and do not pull him through the doors.\n\nIf his fear spreads to the hallway, he stops recovering after the trigger, or pain could be involved, arrange a veterinary check and an individual behavior consultation.",
            'sources' => ['https://avsab.org/resources/position-statements/'],
            'helpful_count' => 31,
            'is_highlighted' => true,
        ]);

        $elevatorComment = ForumComment::factory()->create([
            'topic_id' => $elevator->id,
            'answer_id' => $elevatorExpert->id,
            'author_key' => 'mia-carter',
            'author_name' => 'Mia Carter',
            'author_initials' => 'MC',
            'body' => 'Thank you. He can currently watch the doors from about eight metres away and recover quickly.',
        ]);

        ForumComment::factory()->create([
            'topic_id' => $elevator->id,
            'answer_id' => $elevatorExpert->id,
            'parent_id' => $elevatorComment->id,
            'author_key' => 'eva-jonas',
            'author_name' => 'Eva Jonas',
            'author_initials' => 'EJ',
            'body' => 'That sounds like a useful starting distance. Let his body language set the pace.',
        ]);

        $emergency = ForumTopic::factory()->medical()->create([
            'author_key' => 'tomas-k',
            'author_name' => 'Tomas K.',
            'author_initials' => 'TK',
            'slug' => 'dog-breathing-heavily-and-unable-to-stand',
            'title' => 'My dog is breathing heavily and cannot stand normally',
            'body' => 'This started suddenly tonight. We are calling an emergency clinic now and are not waiting for a forum diagnosis. I am leaving the topic open only for practical transport preparation.',
            'subcategory' => 'symptoms',
            'tags' => ['urgent', 'breathing', 'emergency'],
            'is_urgent' => true,
            'comment_policy' => 'review',
            'view_count' => 192,
            'last_activity_at' => now()->subMinutes(34),
        ]);

        ForumAnswer::factory()->expert()->create([
            'topic_id' => $emergency->id,
            'author_key' => 'dr-emilia',
            'author_name' => 'Dr. Emilia Vaitke',
            'author_initials' => 'EV',
            'author_role' => 'Verified veterinarian',
            'body' => 'Do not wait for replies here. Call the emergency clinic, describe the breathing difficulty, and follow its transport instructions. Keep the airway unobstructed and bring any suspected toxin packaging or medication list.',
            'sources' => [],
            'is_highlighted' => true,
            'helpful_count' => 42,
        ]);

        $travel = ForumTopic::factory()->resolved()->create([
            'author_key' => 'ruta-and-milo',
            'author_name' => 'Ruta and Milo',
            'author_initials' => 'RM',
            'slug' => 'documents-for-dog-travel-lithuania-poland',
            'type' => ForumTopicType::Guide,
            'title' => 'Which documents are needed to travel with a dog from Lithuania to Poland?',
            'body' => 'We are preparing a road trip and collected the official requirements, transport notes, and a checklist. Please check the linked official sources again close to departure because rules can change.',
            'category' => 'travel',
            'subcategory' => 'documents',
            'tags' => ['travel', 'documents', 'lithuania', 'poland'],
            'desired_answer' => 'sources',
            'view_count' => 1284,
            'last_activity_at' => now()->subHours(3),
        ]);

        $travelAnswer = ForumAnswer::factory()->create([
            'topic_id' => $travel->id,
            'author_key' => 'ruta-and-milo',
            'author_name' => 'Ruta and Milo',
            'author_initials' => 'RM',
            'author_role' => 'Experienced pet traveller',
            'body' => "Our checklist includes an ISO-compatible microchip, a valid rabies vaccination recorded after microchipping, and an EU pet passport. We also confirm the carrier's current rules and keep the nearest clinics along the route saved offline.\n\nOfficial rules take precedence over this personal checklist.",
            'experience_type' => 'source-summary',
            'sources' => [
                'https://europa.eu/youreurope/citizens/travel/carry/animal-plant/index_en.htm',
                'https://food.ec.europa.eu/animals/movement-pets/eu-legislation_en',
            ],
            'is_accepted' => true,
            'is_highlighted' => true,
            'helpful_count' => 86,
        ]);
        $travel->update(['accepted_answer_id' => $travelAnswer->id]);

        $birdClinic = ForumTopic::factory()->create([
            'author_key' => 'inga-kesha',
            'author_name' => 'Inga',
            'author_initials' => 'IN',
            'author_role' => 'Kesha owner',
            'slug' => 'bird-veterinarian-in-vilnius',
            'type' => ForumTopicType::Recommendation,
            'title' => 'Which clinic in Vilnius currently accepts parrots?',
            'body' => 'I am looking for a clinic that confirms avian appointments rather than only listing exotic pets generally. This is not an emergency. Lithuanian or English service would work.',
            'category' => 'services',
            'subcategory' => 'clinics',
            'tags' => ['bird', 'clinic', 'vilnius', 'local'],
            'pet_key' => 'kesha',
            'pet_name' => 'Kesha',
            'pet_species' => 'Bird',
            'pet_age_label' => '2 years',
            'location' => 'Vilnius',
            'desired_answer' => 'local-recommendation',
            'has_expert_answer' => true,
            'view_count' => 224,
            'last_activity_at' => now()->subHours(5),
        ]);

        ForumAnswer::factory()->expert()->create([
            'topic_id' => $birdClinic->id,
            'author_key' => 'paws-24-team',
            'author_name' => 'Paws 24 Veterinary Center',
            'author_initials' => 'P24',
            'author_role' => 'Verified clinic representative',
            'expertise' => 'Clinic scheduling',
            'body' => 'Our avian veterinarian is currently available on Tuesdays and Thursdays by appointment. Please call before travelling because emergency coverage and the specialist schedule can change.',
            'qualification_region' => 'Vilnius, Lithuania',
            'helpful_count' => 19,
        ]);

        $carrier = ForumTopic::factory()->resolved()->create([
            'author_key' => 'mia-carter',
            'author_name' => 'Mia Carter',
            'author_initials' => 'MC',
            'author_role' => 'Scout and Nori owner',
            'slug' => 'helping-a-cat-feel-safe-in-a-carrier',
            'title' => 'A gradual plan that helped my cat feel safe in her carrier',
            'type' => ForumTopicType::Journal,
            'body' => 'Nori used to hide as soon as the carrier appeared. We left it open as ordinary furniture, built positive associations in short sessions, and avoided forcing her inside. This diary records what changed over six weeks.',
            'category' => 'behavior',
            'subcategory' => 'fear',
            'tags' => ['cat', 'carrier', 'adaptation', 'cooperative-care'],
            'pet_key' => 'nori',
            'pet_name' => 'Nori',
            'pet_species' => 'Cat',
            'pet_age_label' => '2 years',
            'has_expert_answer' => true,
            'view_count' => 942,
            'last_activity_at' => now()->subDay(),
            'media' => [[
                'type' => 'image',
                'path' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1400&q=85',
                'alt' => 'A relaxed cat resting beside a soft blanket',
                'sensitive' => false,
            ]],
        ]);

        $carrierAnswer = ForumAnswer::factory()->expert()->create([
            'topic_id' => $carrier->id,
            'author_key' => 'sofia-behavior',
            'author_name' => 'Sofia Arden',
            'author_initials' => 'SA',
            'author_role' => 'Verified feline behavior consultant',
            'expertise' => 'Feline behavior and cooperative care',
            'body' => 'The useful part of this diary is choice: the carrier stays available, rewards arrive around it, and the door is introduced only after the cat enters comfortably. Progress should be measured by relaxed behavior, not speed.',
            'sources' => ['https://catvets.com/resource/feline-behavior-guidelines/'],
            'is_accepted' => true,
            'is_highlighted' => true,
            'helpful_count' => 64,
        ]);
        $carrier->update(['accepted_answer_id' => $carrierAnswer->id]);

        ForumTopic::factory()->create([
            'author_key' => 'ana-volunteer',
            'author_name' => 'Ana P.',
            'author_initials' => 'AP',
            'slug' => 'welcoming-a-senior-shelter-dog',
            'title' => 'What made the first week easier for your senior shelter dog?',
            'type' => ForumTopicType::Discussion,
            'body' => 'I am preparing a quiet room, a predictable routine, and a veterinary appointment. I would value personal experiences about pacing introductions without assuming every senior dog needs the same plan.',
            'category' => 'adoption',
            'subcategory' => 'first-days',
            'tags' => ['senior pet', 'adoption', 'first days'],
            'view_count' => 407,
            'last_activity_at' => now()->subDays(2),
        ]);

        ForumTopic::factory()->create([
            'author_key' => 'martynas-tech',
            'author_name' => 'Martynas',
            'author_initials' => 'MA',
            'slug' => 'gps-collar-features-that-matter-on-trails',
            'title' => 'Which GPS collar features actually matter on forest trails?',
            'type' => ForumTopicType::Comparison,
            'body' => 'I care about battery life, coverage gaps, waterproofing, safe home-zone privacy, and export controls. Please disclose any brand relationship and include the date when pricing or coverage was checked.',
            'category' => 'services',
            'subcategory' => 'technology',
            'tags' => ['GPS', 'travel', 'gear', 'privacy'],
            'view_count' => 316,
            'last_activity_at' => now()->subDays(3),
        ]);

        ForumTopic::factory()->draft()->create([
            'author_key' => 'mia-carter',
            'author_name' => 'Mia Carter',
            'author_initials' => 'MC',
            'author_role' => 'Scout and Nori owner',
            'slug' => 'quiet-winter-walk-checklist-draft',
            'title' => 'A quiet winter walk checklist for senior dogs in Vilnius',
            'body' => 'Draft notes about cleared paths, lighting, paw protection, water availability, and shorter return options.',
            'category' => 'walks',
            'tags' => ['winter', 'senior pet', 'vilnius'],
        ]);

        ForumEngagement::factory()->create([
            'topic_id' => $travel->id,
            'user_key' => 'mia-carter',
            'is_bookmarked' => true,
            'subscription_level' => ForumSubscriptionLevel::Digest,
        ]);

        ForumEngagement::factory()->create([
            'topic_id' => $elevator->id,
            'user_key' => 'mia-carter',
            'is_bookmarked' => false,
            'subscription_level' => ForumSubscriptionLevel::All,
        ]);

        ForumNotification::factory()->create([
            'topic_id' => $elevator->id,
            'user_key' => 'mia-carter',
            'type' => 'expert-answer',
            'title' => 'A verified behavior consultant replied',
            'body' => 'Eva added a low-pressure starting plan for Scout.',
            'deduplication_key' => 'seed:elevator:expert:mia',
        ]);

        ForumNotification::factory()->create([
            'topic_id' => $carrier->id,
            'user_key' => 'mia-carter',
            'type' => 'author-reminder',
            'title' => 'Your carrier topic is ready for the knowledge base',
            'body' => 'The accepted answer and sources can become an editorial guide.',
            'deduplication_key' => 'seed:carrier:knowledge:mia',
        ]);

        $travelArticle = KnowledgeArticle::factory()->create([
            'source_topic_id' => $travel->id,
            'slug' => 'dog-travel-documents-lithuania-to-poland',
            'title' => 'Dog travel documents: Lithuania to Poland',
            'summary' => 'A practical pre-trip checklist with official EU sources and reminders to verify current carrier rules.',
            'body' => "Quick answer\n\nPrepare an ISO-compatible microchip, a valid rabies vaccination recorded after microchipping, and an EU pet passport. Check official requirements close to departure.\n\nBefore leaving\n\nConfirm transport rules, save clinic contacts, carry water and medication instructions, and keep digital copies separate from originals.\n\nWhat to avoid\n\nDo not rely on an old forum post as the final legal source. Border and carrier requirements can change.",
            'category' => 'travel',
            'type' => 'checklist',
            'difficulty' => 'beginner',
            'audience' => 'EU pet owners planning a road trip',
            'tags' => ['travel', 'documents', 'eu'],
            'sources' => [
                'https://europa.eu/youreurope/citizens/travel/carry/animal-plant/index_en.htm',
                'https://food.ec.europa.eu/animals/movement-pets/eu-legislation_en',
            ],
            'contributors' => [
                ['name' => 'Ruta and Milo', 'role' => 'community contributor'],
                ['name' => 'PawCircle editorial team', 'role' => 'editor'],
            ],
            'last_reviewed_at' => now()->subDays(4),
            'next_review_at' => now()->addMonths(3),
        ]);

        $carrierArticle = KnowledgeArticle::factory()->create([
            'source_topic_id' => $carrier->id,
            'slug' => 'help-a-cat-feel-safe-in-a-carrier',
            'title' => 'Help a cat feel safe in a carrier',
            'summary' => 'A gradual, choice-based routine for turning the carrier into a familiar resting place.',
            'body' => "Quick answer\n\nKeep the carrier open in everyday space, make it comfortable, and reward voluntary investigation. Work in short steps and close the door only after relaxed entry is reliable.\n\nStep by step\n\nStart with distance, then reward looking, approaching, stepping inside, and resting. Add tiny door movements later. Stop before the cat tries to escape.\n\nWhen to ask for help\n\nIf transport is urgent, fear is severe, or handling risks injury, ask a veterinarian or qualified feline behavior professional for an individual plan.",
            'category' => 'behavior',
            'type' => 'guide',
            'difficulty' => 'beginner',
            'audience' => 'Cat owners preparing for travel or clinic visits',
            'tags' => ['cat', 'carrier', 'cooperative-care'],
            'sources' => ['https://catvets.com/resource/feline-behavior-guidelines/'],
            'contributors' => [
                ['name' => 'Mia Carter', 'role' => 'community contributor'],
                ['name' => 'Sofia Arden', 'role' => 'expert reviewer'],
            ],
            'last_reviewed_at' => now()->subWeek(),
            'next_review_at' => now()->addMonths(6),
        ]);

        KnowledgeVersion::factory()->create([
            'article_id' => $travelArticle->id,
            'title' => $travelArticle->title,
            'body' => $travelArticle->body,
            'edited_by' => 'PawCircle editorial team',
            'change_summary' => 'Initial checklist reviewed against current EU sources.',
        ]);

        KnowledgeVersion::factory()->create([
            'article_id' => $carrierArticle->id,
            'title' => $carrierArticle->title,
            'body' => $carrierArticle->body,
            'edited_by' => 'Sofia Arden',
            'change_summary' => 'Initial guide with a choice-based handling review.',
        ]);
    }
}
