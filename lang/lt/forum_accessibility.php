<?php

declare(strict_types=1);

return [
    'validation' => [
        'summary' => 'Peržiūrėkite pažymėtus laukus.',
        'media_description_required' => 'Aprašykite įkeltą mediją žmonėms, kurie jos nemato.',
        'video_transcript_required' => 'Pateikite vaizdo įrašo tekstinę išklotinę arba lygiavertį aprašą.',
        'caption_video_required' => 'Subtitrų failą galima pridėti tik kartu su vaizdo įrašu.',
        'caption_locale_required' => 'Pasirinkite subtitrų failo kalbą.',
        'caption_file_required' => 'Prieš nustatydami kalbą pasirinkite subtitrų failą.',
        'invalid_webvtt' => 'Subtitrų failas turi būti tinkamas WebVTT failas, prasidedantis WEBVTT.',
        'media_storage_failed' => 'Nepavyko saugiai išsaugoti medijos failo. Bandykite dar kartą.',
    ],
    'media' => [
        'description' => 'Medijos aprašas',
        'description_help' => 'Aprašykite prasmingą vaizdinį turinį neįtraukdami privačios informacijos.',
        'video_transcript' => 'Vaizdo įrašo išklotinė arba lygiavertis tekstas',
        'video_transcript_help' => 'Įtraukite kalbą, prasmingus garsus ir vaizdinę informaciją, reikalingą įrašui suprasti.',
        'caption_file' => 'WebVTT subtitrų failas',
        'caption_file_help' => 'Pasirenkami laiko žymomis susieti WebVTT subtitrai, iki 256 KB.',
        'caption_locale' => 'Subtitrų kalba',
        'captions_label' => 'Subtitrai, kalba: :locale',
        'transcript_label' => 'Vaizdo įrašo išklotinė',
        'legacy_description' => 'Forumo medija, kuria pasidalijo temos autorius.',
    ],
    'tables' => [
        'reports' => 'Pateikti forumo pranešimai',
        'moderation_cases' => 'Atviros moderavimo bylos',
        'appeals' => 'Moderavimo apeliacijos',
        'categories' => 'Forumo kategorijų administravimas',
        'guides' => 'Žinių vadovų administravimas',
        'taxonomy_imports' => 'Gyvūnų taksonomijos importai',
        'professional_verifications' => 'Specialistų patvirtinimo peržiūros',
    ],
];
