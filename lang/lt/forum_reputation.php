<?php

declare(strict_types=1);

$dimensions = [
    'helpfulness' => 'Naudingumas',
    'answer-quality' => 'Atsakymų kokybė',
    'reliability' => 'Patikimumas',
    'evidence-quality' => 'Įrodymų kokybė',
    'empathy' => 'Empatija',
    'respectful-communication' => 'Pagarbus bendravimas',
    'community-support' => 'Bendruomenės palaikymas',
    'species-experience' => 'Konkrečios rūšies patirtis',
    'category-expertise' => 'Kategorijos kompetencija',
    'local-knowledge' => 'Vietos žinios',
    'rescue-contribution' => 'Indėlis į gelbėjimą',
    'lost-found-contribution' => 'Indėlis ieškant dingusių gyvūnų',
    'adoption-support' => 'Parama įvaikinimui',
    'mentoring' => 'Mentorystė',
    'guide-contribution' => 'Indėlis į vadovus',
    'correction-contribution' => 'Indėlis į pataisymus',
    'moderation-contribution' => 'Indėlis į moderavimą',
    'marketplace-trust' => 'Prekyvietės patikimumas',
    'service-review-reliability' => 'Paslaugų apžvalgų patikimumas',
    'event-reliability' => 'Renginių patikimumas',
    'professional-contribution' => 'Profesinis indėlis',
];
$trustLevels = [
    'new-member' => 'Naujas narys',
    'member' => 'Narys',
    'established-member' => 'Patyręs narys',
    'trusted-contributor' => 'Patikimas bendradarbis',
    'mentor' => 'Mentorius',
    'community-reviewer' => 'Bendruomenės vertintojas',
    'category-steward' => 'Kategorijos prižiūrėtojas',
    'moderator' => 'Moderatorius',
    'senior-moderator' => 'Vyresnysis moderatorius',
    'verified-professional' => 'Patvirtintas specialistas',
    'organization-representative' => 'Organizacijos atstovas',
    'administrator' => 'Administratorius',
];
$badges = [
    'onboarding' => 'Įvadinė dalis baigta',
    'helpful-contributor' => 'Naudingas bendradarbis',
    'detailed-answer' => 'Išsamus atsakymas',
    'evidence-contributor' => 'Įrodymų teikėjas',
    'guide-author' => 'Vadovo autorius',
    'guide-reviewer' => 'Vadovo vertintojas',
    'translator' => 'Vertėjas',
    'mentor' => 'Mentorius',
    'foster-supporter' => 'Laikinos globos rėmėjas',
    'rescue-volunteer' => 'Gelbėjimo savanoris',
    'lost-animal-search-supporter' => 'Dingusių gyvūnų paieškos rėmėjas',
    'successful-reunion-contributor' => 'Prisidėjo prie sėkmingo sugrįžimo',
    'adoption-supporter' => 'Įvaikinimo rėmėjas',
    'senior-animal-supporter' => 'Vyresnių gyvūnų rėmėjas',
    'special-needs-supporter' => 'Specialiųjų poreikių rėmėjas',
    'local-guide' => 'Vietos gidas',
    'event-organizer' => 'Renginių organizatorius',
    'accessibility-contributor' => 'Prieinamumo bendradarbis',
    'community-reviewer' => 'Bendruomenės vertintojas',
    'category-steward' => 'Kategorijos prižiūrėtojas',
    'marketplace-reliability' => 'Prekyvietės patikimumas',
];

return [
    'dimensions' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "Patvirtintas indėlis srityje „{$name}“, vertinamas atskirai nuo kitų kompetencijų.",
        ],
        $dimensions,
    ),
    'trust_levels' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "Lygis „{$name}“ vertinamas atskirai nuo karmos ir profesinių pažymėjimų.",
        ],
        $trustLevels,
    ),
    'badges' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "Ženklas „{$name}“ suteikiamas pagal patvirtintus kriterijus ir gali būti atšauktas nustačius piktnaudžiavimą.",
        ],
        $badges,
    ),
    'events' => [
        'helpful_vote' => 'Kitas narys pažymėjo atsakymą kaip naudingą.',
        'answer_accepted' => 'Temos autorius priėmė atsakymą.',
        'reversal' => 'Ankstesnis reputacijos įvykis atšauktas išsaugant audito įrašą.',
    ],
    'messages' => [
        'self_award_forbidden' => 'Negalite suteikti reputacijos sau.',
        'self_vote_forbidden' => 'Negalite vertinti savo atsakymo.',
        'self_accept_forbidden' => 'Negalite priimti savo atsakymo.',
        'relationship_limit_reached' => 'Šis ryšys pasiekė dabartinę reputacijos poveikio ribą.',
        'invalid_confirmation_risk' => 'Pasirinkta patvirtinimo rizikos klasė nepalaikoma.',
        'invalid_confirmation_quorum' => 'Patvirtinimo kvorumą turi sudaryti nuo 2 iki 50 vertintojų.',
        'invalid_confirmation_diversity' => 'Patvirtinimo įvairovės reikalavimas turi atitikti pasirinktą kvorumą.',
        'invalid_confirmation_stance' => 'Pasirinkite palaikyti, prieštarauti arba susilaikyti.',
        'confirmation_conflict_required' => 'Prieš pateikdami vertinimą aprašykite interesų konfliktą.',
        'confirmation_closed' => 'Šis patvirtinimas uždarytas arba jo galiojimas pasibaigė.',
        'duplicate_confirmation_vote' => 'Jūs jau įvertinote šį patvirtinimą.',
    ],
];
