<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourcePath = $root.'/docs/requirements/forum-source-prompt.md';
$outputPath = $root.'/resources/data/forum/categories.json';
$checkOnly = in_array('--check', $argv, true);

$source = file_get_contents($sourcePath);

if ($source === false) {
    fwrite(STDERR, "Unable to read the preserved forum source prompt.\n");
    exit(1);
}

preg_match('/<forum-source-extension>\n(.*)\n<\/forum-source-extension>/sU', $source, $match);

if (! isset($match[1])) {
    fwrite(STDERR, "The additive forum source section is missing.\n");
    exit(1);
}

$categories = originalCategories();
$extension = $match[1];
$headingPattern = '/^##\s+\d+\.\s+additional first-level category\s+(\d+):\s+(.+)$/mi';
preg_match_all($headingPattern, $extension, $headings, PREG_OFFSET_CAPTURE);

foreach ($headings[0] as $index => $heading) {
    $number = (int) $headings[1][$index][0];

    if ($number < 21 || $number > 44) {
        continue;
    }

    $start = $heading[1] + strlen($heading[0]);
    $remaining = substr($extension, $start);
    preg_match('/\R##\s+/u', $remaining, $nextHeading, PREG_OFFSET_CAPTURE);
    $end = isset($nextHeading[0][1])
        ? $start + $nextHeading[0][1]
        : strlen($extension);
    $section = substr($extension, $start, $end - $start);

    preg_match('/stable key:\s*\R+\s*-\s*`([^`]+)`/iu', $section, $keyMatch);
    preg_match('/purpose:\s*\R+\s*(.+)/iu', $section, $purposeMatch);
    preg_match('/subcategories[^\r\n]*\R+(.*)/isu', $section, $subcategoryBlockMatch);

    if (! isset($keyMatch[1], $purposeMatch[1], $subcategoryBlockMatch[1])) {
        fwrite(STDERR, "Unable to parse category {$number}.\n");
        exit(1);
    }

    preg_match_all(
        '/^\s*\d+\.\s+(.+)$/mu',
        $subcategoryBlockMatch[1],
        $subcategoryMatches,
    );

    $name = trim($headings[2][$index][0]);
    $categories[] = category(
        number: $number,
        stableKey: trim($keyMatch[1]),
        slug: str_replace('forum.', '', trim($keyMatch[1])),
        name: $name,
        purpose: trim($purposeMatch[1]),
        subcategories: array_map('trim', $subcategoryMatches[1]),
    );
}

usort(
    $categories,
    static fn (array $left, array $right): int => $left['number'] <=> $right['number'],
);

$numbers = array_column($categories, 'number');
$keys = array_column($categories, 'stable_key');

if ($numbers !== range(1, 44) || count($keys) !== count(array_unique($keys))) {
    fwrite(STDERR, "The category manifest must contain unique roots 1 through 44.\n");
    exit(1);
}

$subcategoryCount = array_sum(array_map(
    static fn (array $category): int => count($category['subcategories']),
    $categories,
));

$json = json_encode([
    'schema_version' => 1,
    'source_payload_sha256' => '6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773',
    'root_category_count' => count($categories),
    'subcategory_count' => $subcategoryCount,
    'categories' => $categories,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";

if ($checkOnly) {
    if (! is_file($outputPath) || file_get_contents($outputPath) !== $json) {
        fwrite(STDERR, "{$outputPath} is stale.\n");
        exit(1);
    }

    fwrite(STDOUT, "Verified 44 forum roots and {$subcategoryCount} subcategories.\n");
    exit(0);
}

$directory = dirname($outputPath);

if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
    fwrite(STDERR, "Unable to create {$directory}.\n");
    exit(1);
}

if (file_put_contents($outputPath, $json, LOCK_EX) === false) {
    fwrite(STDERR, "Unable to write {$outputPath}.\n");
    exit(1);
}

fwrite(STDOUT, "Generated 44 forum roots and {$subcategoryCount} subcategories.\n");

/**
 * @return list<array{
 *     number: int,
 *     stable_key: string,
 *     slug: string,
 *     name: string,
 *     purpose: string,
 *     icon: string,
 *     source: string,
 *     subcategories: list<array{stable_key: string, slug: string, name: string}>
 * }>
 */
function originalCategories(): array
{
    $definitions = [
        [1, 'forum.health', 'health', 'Health', 'Health preparation, symptoms, veterinary care, recovery, and prevention.', 'heart-pulse', [
            'general health', 'symptoms and next steps', 'preventive care', 'veterinary visits', 'diagnostic tests', 'vaccinations', 'parasite control', 'medication safety', 'surgery preparation', 'recovery and aftercare', 'dental health', 'skin and coat health', 'eyes and ears', 'mobility and orthopedics', 'chronic conditions', 'pain and comfort', 'senior health', 'reproductive health', 'second opinions', 'specialist referrals',
        ]],
        [2, 'forum.nutrition', 'nutrition', 'Nutrition', 'Safe feeding, nutrition evidence, weight, hydration, and dietary planning.', 'utensils', [
            'daily feeding', 'species-specific nutrition', 'life-stage nutrition', 'weight management', 'diet transitions', 'allergies and sensitivities', 'therapeutic diets', 'raw food safety', 'home-prepared diets', 'commercial food', 'treats', 'supplements', 'hydration', 'feeding behavior', 'toxic foods', 'newborn feeding', 'senior nutrition', 'feeding equipment',
        ]],
        [3, 'forum.behavior', 'behavior', 'Behavior', 'Animal behavior, body language, emotional welfare, and safe professional support.', 'brain', [
            'body language', 'fear and confidence', 'anxiety', 'aggression and safety', 'reactivity', 'separation-related behavior', 'socialization', 'safe introductions', 'resource guarding', 'vocalization', 'destructive behavior', 'toileting behavior', 'repetitive behavior', 'sleep and rest', 'multi-animal households', 'enrichment', 'behavior assessment', 'professional behavior support',
        ]],
        [4, 'forum.training-education', 'training-education', 'Training and education', 'Humane learning, handling, skills, classes, and structured training plans.', 'graduation-cap', [
            'training foundations', 'positive reinforcement', 'recall', 'leash skills', 'house training', 'cooperative care', 'carrier and crate training', 'home manners', 'public manners', 'tricks', 'sport and agility', 'working skills', 'training plans', 'training tools', 'classes and instructors', 'young animals', 'adult animals', 'senior animals',
        ]],
        [5, 'forum.everyday-care', 'everyday-care', 'Everyday care', 'Daily routines, grooming, hygiene, enrichment, and practical household care.', 'sparkles', [
            'daily routines', 'grooming', 'bathing', 'nail and hoof care', 'coat care', 'oral care', 'toilet and litter routines', 'sleep areas', 'feeding routines', 'enrichment', 'handling', 'seasonal care', 'senior care', 'newborn care', 'multi-animal routines', 'care products', 'temporary caregivers',
        ]],
        [6, 'forum.walks-exercise-places', 'walks-exercise-places', 'Walks, exercise, and places', 'Safe movement, outdoor access, exercise, routes, and animal-friendly places.', 'map-pinned', [
            'walking basics', 'leash walking', 'off-leash safety', 'exercise needs', 'fitness', 'routes', 'parks', 'trails', 'beaches', 'animal-friendly places', 'play areas', 'meetups', 'weather safety', 'night walks', 'urban walks', 'rural walks', 'accessibility', 'local hazards',
        ]],
        [7, 'forum.travel-documents', 'travel-documents', 'Travel and documents', 'Safe transport, accommodation, border requirements, identification, and travel planning.', 'luggage', [
            'travel planning', 'identification documents', 'passports', 'vaccination documents', 'microchips', 'border rules', 'air travel', 'rail travel', 'road travel', 'public transport', 'carriers and crates', 'accommodation', 'travel health', 'quarantine rules', 'insurance', 'relocation', 'temporary care', 'country-specific requirements',
        ]],
        [8, 'forum.adoption-rescue-shelters', 'adoption-rescue-shelters', 'Adoption, rescue, and shelters', 'Responsible adoption, fostering, rescue coordination, shelter support, and rehoming.', 'house-heart', [
            'adoption preparation', 'adoption listings', 'shelter adoption', 'rescue adoption', 'private rehoming', 'fostering', 'first days', 'adaptation', 'application process', 'home checks', 'contracts', 'fees and transparency', 'special-needs adoption', 'senior adoption', 'failed adoption support', 'returns and follow-up', 'shelter support', 'rescue operations',
        ]],
        [9, 'forum.lost-found', 'lost-found', 'Lost and found', 'Structured lost, found, sighting, search, reunion, and fraud-prevention support.', 'scan-search', [
            'lost animal', 'found animal', 'animal sighting', 'stolen animal', 'search planning', 'search coordination', 'volunteers', 'posters and sharing', 'shelter contacts', 'clinic contacts', 'microchip lookup', 'location privacy', 'reward safety', 'false sightings', 'duplicate cases', 'reunions', 'case closure', 'prevention',
        ]],
        [10, 'forum.breeding-genetics-newborn-care', 'breeding-genetics-newborn-care', 'Breeding, genetics, and newborn care', 'Evidence-based genetics, ethical breeding, pregnancy, birth, and newborn care.', 'dna', [
            'breeding ethics', 'genetics', 'hereditary conditions', 'health screening', 'mate selection', 'breeding regulations', 'fertility', 'pregnancy', 'birth preparation', 'labor and delivery', 'newborn care', 'hand rearing', 'litter care', 'weaning', 'early socialization', 'record keeping', 'breeder verification', 'emergency planning',
        ]],
        [11, 'forum.species-breed-communities', 'species-breed-communities', 'Species and breed communities', 'Species, breed, variety, life-stage, and community-specific discussions.', 'paw-print', [
            'dogs', 'cats', 'birds', 'rabbits', 'rodents', 'ferrets', 'reptiles', 'amphibians', 'fish', 'horses', 'farm animals', 'invertebrates', 'exotic animals', 'mixed breeds', 'unknown breeds', 'breed identification', 'breed health', 'breed behavior', 'species clubs',
        ]],
        [12, 'forum.services-professionals', 'services-professionals', 'Services and professionals', 'Finding, evaluating, verifying, and working with animal-care services.', 'briefcase-medical', [
            'veterinary clinics', 'emergency clinics', 'specialists', 'trainers', 'behavior consultants', 'groomers', 'pet sitters', 'dog walkers', 'boarding', 'transport providers', 'rehabilitation', 'farriers', 'insurance', 'service directories', 'choosing a professional', 'credentials', 'appointments', 'service complaints',
        ]],
        [13, 'forum.gear-products-technology', 'gear-products-technology', 'Gear, products, and technology', 'Product selection, safety, reviews, repairability, and responsible technology use.', 'cpu', [
            'collars and harnesses', 'leashes', 'carriers and crates', 'beds', 'feeding products', 'grooming products', 'toys and enrichment', 'mobility products', 'clothing and protection', 'gps trackers', 'smart devices', 'cameras', 'aquarium equipment', 'terrarium equipment', 'product safety', 'recalls', 'comparisons', 'repairs', 'privacy and security',
        ]],
        [14, 'forum.marketplace-exchanges', 'marketplace-exchanges', 'Marketplace and exchanges', 'Safe listings, exchanges, completed transactions, disputes, and prohibited-item controls.', 'store', [
            'buying', 'selling', 'giving away', 'wanted listings', 'reservations', 'local pickup', 'delivery', 'completed transactions', 'cancellations', 'refunds', 'disputes', 'buyer feedback', 'seller feedback', 'scam prevention', 'prohibited items', 'prohibited animal sales', 'second-hand equipment', 'equipment lending',
        ]],
        [15, 'forum.community-stories-daily-life', 'community-stories-daily-life', 'Community, stories, and daily life', 'Constructive stories, introductions, journals, photos, milestones, and everyday community life.', 'messages-square', [
            'introductions', 'daily stories', 'photos', 'videos', 'milestones', 'success stories', 'funny moments', 'training progress', 'recovery progress', 'adoption stories', 'rescue stories', 'senior life', 'multi-animal life', 'seasonal life', 'community questions', 'memorial stories', 'creative work', 'journals',
        ]],
        [16, 'forum.owner-support-wellbeing', 'owner-support-wellbeing', 'Owner support and wellbeing', 'Emotional support, caregiver wellbeing, grief, difficult decisions, and practical mutual support.', 'heart-handshake', [
            'caregiver support', 'caregiver fatigue', 'compassion fatigue', 'volunteer burnout', 'financial stress', 'long-term treatment stress', 'lost-animal stress', 'adoption adaptation', 'family disagreements', 'difficult decisions', 'hospice support', 'pet loss', 'memorial support', 'peer support', 'professional mental-health support', 'boundaries and self-care',
        ]],
        [17, 'forum.laws-rights-animal-welfare', 'laws-rights-animal-welfare', 'Laws, rights, and animal welfare', 'Jurisdiction-aware law, ownership rights, welfare duties, reporting, and policy discussion.', 'scale', [
            'animal welfare law', 'ownership law', 'housing law', 'travel law', 'breeding law', 'animal sales law', 'wildlife law', 'assistance-animal access', 'licensing', 'permits', 'identification requirements', 'neglect reporting', 'cruelty reporting', 'legal disputes', 'insurance law', 'country-specific guidance', 'policy proposals', 'legal information safety',
        ]],
        [18, 'forum.emergencies-safety-alerts', 'emergencies-safety-alerts', 'Emergencies and safety alerts', 'Urgent safety information with clear professional and emergency-service boundaries.', 'shield-alert', [
            'veterinary emergency', 'poisoning', 'missing animal', 'animal cruelty', 'evacuation', 'urgent foster', 'blood donor request', 'disease outbreak', 'dangerous product alert', 'weather emergency', 'fire safety', 'transport emergency', 'wildlife emergency', 'public-health alert', 'emergency contacts', 'preparedness', 'alert updates', 'resolved alerts',
        ]],
        [19, 'forum.events-clubs-activities', 'events-clubs-activities', 'Events, clubs, and activities', 'Accessible, safe, welfare-aware events, clubs, registrations, and community activities.', 'calendar-days', [
            'local events', 'online events', 'meetups', 'clubs', 'training classes', 'workshops', 'webinars', 'professional conferences', 'fundraisers', 'adoption events', 'volunteer events', 'competitions', 'walks', 'family activities', 'registrations', 'waitlists', 'accessibility', 'welfare rules', 'event reviews',
        ]],
        [20, 'forum.platform-help-support', 'platform-help-support', 'Platform help and support', 'Account, privacy, accessibility, safety, reporting, and feature support for PawCircle.', 'circle-help', [
            'getting started', 'account access', 'profile settings', 'privacy settings', 'security settings', 'notifications', 'forum posting', 'media uploads', 'search help', 'accessibility help', 'language settings', 'reporting content', 'appeals help', 'professional verification help', 'organization help', 'data export', 'account deletion', 'bug reports', 'feature requests', 'community rules',
        ]],
    ];

    return array_map(
        static fn (array $definition): array => category(...$definition, source: 'recovered-primary-and-master'),
        $definitions,
    );
}

/**
 * @param  list<string>  $subcategories
 * @return array{
 *     number: int,
 *     stable_key: string,
 *     slug: string,
 *     name: string,
 *     purpose: string,
 *     icon: string,
 *     source: string,
 *     subcategories: list<array{stable_key: string, slug: string, name: string}>
 * }
 */
function category(
    int $number,
    string $stableKey,
    string $slug,
    string $name,
    string $purpose,
    string $icon = 'message-circle',
    array $subcategories = [],
    string $source = 'additive-master-extension',
): array {
    $children = [];
    $usedSlugs = [];

    foreach ($subcategories as $subcategoryName) {
        $childSlug = slug($subcategoryName);
        $candidate = $childSlug;
        $suffix = 2;

        while (isset($usedSlugs[$candidate])) {
            $candidate = $childSlug.'-'.$suffix;
            $suffix++;
        }

        $usedSlugs[$candidate] = true;
        $children[] = [
            'stable_key' => $stableKey.'.'.str_replace('-', '.', $candidate),
            'slug' => $slug.'/'.$candidate,
            'name' => $subcategoryName,
        ];
    }

    return [
        'number' => $number,
        'stable_key' => $stableKey,
        'slug' => $slug,
        'name' => $name,
        'purpose' => $purpose,
        'icon' => $icon,
        'source' => $source,
        'subcategories' => $children,
    ];
}

function slug(string $value): string
{
    $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

    if ($slug === '') {
        throw new RuntimeException("Unable to generate a stable slug for '{$value}'.");
    }

    return $slug;
}
