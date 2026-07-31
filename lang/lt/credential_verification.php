<?php

declare(strict_types=1);

return [
    'type' => [
        'identity' => 'Valstybinis tapatybės dokumentas',
        'education' => 'Išsilavinimo dokumentas',
        'qualification' => 'Profesinė kvalifikacija',
        'license' => 'Profesinė licencija',
        'workplace' => 'Darbo santykiai',
        'contact' => 'Profesinis kontaktas',
        'organization-role' => 'Pareigos organizacijoje',
        'organization-registration' => 'Organizacijos registracija',
        'rescue-organization' => 'Gyvūnų gelbėjimo organizacija',
        'shelter' => 'Gyvūnų prieglauda',
        'breeder' => 'Veisėjo registracija',
        'organization-representative' => 'Organizacijos atstovas',
    ],
    'profile_status' => [
        'unsubmitted' => 'Nepateikta',
        'submitted' => 'Dokumentai pateikti',
        'in-review' => 'Tikrinama',
        'more-information' => 'Reikia daugiau informacijos',
        'partially-verified' => 'Patvirtinta iš dalies',
        'verified' => 'Patvirtinimas galioja',
        'expiring' => 'Patvirtinimą reikia atnaujinti',
        'suspended' => 'Patvirtinimas sustabdytas',
        'rejected' => 'Dokumentai nepriimti',
    ],
    'status' => [
        'submitted' => 'Pateikta',
        'in-review' => 'Tikrinama',
        'verified' => 'Patvirtinta',
        'expiring' => 'Netrukus reikės atnaujinti',
        'expired' => 'Galiojimas pasibaigęs',
        'rejected' => 'Atmesta',
        'suspended' => 'Sustabdyta',
        'revoked' => 'Patvirtinimas panaikintas',
    ],
    'reason' => [
        'approved' => 'Dokumentas patvirtintas po nepriklausomo patikrinimo.',
        'expired' => 'Pasibaigė nurodytas dokumento galiojimo laikas.',
        'information-required' => 'Reikia papildomos informacijos apie dokumentą.',
        'rejected' => 'Dokumento įrodymai atmesti po nepriklausomo patikrinimo.',
        'renewed' => 'Atnaujinimui pateiktas naujas dokumentas.',
        'revoked' => 'Po patikrinimo dokumento patvirtinimas panaikintas.',
        'suspended' => 'Dokumento patvirtinimas sustabdytas iki patikrinimo.',
    ],
    'validation' => [
        'appeal_exists' => 'Dėl šio dokumento jau pateikta nagrinėjama apeliacija.',
        'appeal_status' => 'Dėl šio dokumento apeliacijos pateikti negalima.',
        'conflict' => 'Tikrinantis asmuo negali tikrinti savo profesinio profilio.',
        'expiry' => 'Patvirtinto dokumento galiojimo data turi būti ateityje, jei ji nurodyta.',
        'original_reviewer' => 'Pradinis tikrintojas negali būti vienintelis apeliacijos tikrintojas.',
        'transition' => 'Toks dokumento būsenos pakeitimas neleidžiamas.',
    ],
];
