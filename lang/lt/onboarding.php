<?php

declare(strict_types=1);

return [
    'page' => [
        'title' => 'Paruoškite savo „PawCircle“ paskyrą',
        'eyebrow' => 'Privati pradžia',
        'description' => 'Prieš įeidami į bendruomenę patvirtinkite kelis paskyros pasirinkimus. Po kiekvieno žingsnio eiga saugiai išsaugoma.',
        'resume_note' => 'Galite atsijungti ir vėliau tęsti nuo šio žingsnio.',
        'logout' => 'Atsijungti',
    ],
    'progress' => [
        'label' => 'Paskyros paruošimo eiga',
        'step' => ':current žingsnis iš :total',
    ],
    'steps' => [
        'introduction' => [
            'label' => 'Pradžia',
            'title' => 'Pradėkite valdydami savo informaciją',
            'body' => '„PawCircle“ atskiria paskyros, augintinio, priežiūros ir socialines tapatybes. Šiame etape neskelbiamas augintinis, tiksli vieta ar privatūs priežiūros įrašai.',
            'acknowledgement' => 'Suprantu, kad vėliau galėsiu peržiūrėti ir pakeisti šiuos paskyros pasirinkimus.',
            'continue' => 'Tęsti prie nuostatų',
        ],
        'preferences' => [
            'label' => 'Nuostatos',
            'title' => 'Pasirinkite kalbą ir laiko juostą',
            'body' => 'Šios nuostatos valdo sąsajos kalbą ir datų bei laiko rodymą. Jos nekeičia išsaugoto įvykių laiko.',
            'save' => 'Išsaugoti ir tęsti',
        ],
        'pet_relationship' => [
            'label' => 'Ryšys su augintiniu',
            'title' => 'Susiekite augintinį, kai būsite pasirengę',
            'body' => 'Galite sukurti privatų augintinio profilį, rasti esamą profilį ir paprašyti prieigos arba tęsti be augintinio. Ši galimybė liks prieinama ir vėliau.',
            'create_or_find' => 'Sukurti arba rasti augintinio profilį',
            'managed_pet' => 'Tęsti su mano valdomu augintiniu',
            'access_requested' => 'Tęsti su mano prieigos prašymu',
            'not_now' => 'Kol kas tęsti be augintinio',
        ],
        'privacy_discovery' => [
            'label' => 'Privatumas',
            'title' => 'Patvirtinkite paieškos ir kontakto pasirinkimus',
            'body' => 'Iš pradžių visos parinktys išjungtos. Įjunkite tik tuos būdus, kuriais norite būti randami ar pasiekiami kitų narių.',
            'discoverable_label' => 'Rodyti mano paskyrą narių paieškoje',
            'discoverable_description' => 'Leidžia tinkamiems nariams rasti viešai saugią jūsų paskyros profilio dalį.',
            'recommendable_label' => 'Įtraukti mano paskyrą į rekomendacijas',
            'recommendable_description' => 'Leidžia „PawCircle“ rekomenduoti viešai saugų paskyros profilį tinkamiems nariams.',
            'messages_label' => 'Leisti žinučių užklausas',
            'messages_description' => 'Leidžia tinkamiems nariams už esamų ryšių ribų paprašyti pradėti pokalbį.',
            'protected_data' => 'Paskyros ir augintinio duomenys nėra automatiškai viešinami. Tiksli vieta, medicininiai įrašai ir GPS duomenys lieka privatūs, kol jais aiškiai nepasidalijate. Išorinis indeksavimas lieka išjungtas, kol jo neįjungiate palaikomame profilio nustatyme.',
            'acknowledgement' => 'Suprantu šias privatumo ribas ir patvirtinu savo paieškos bei kontakto pasirinkimus.',
            'save' => 'Baigti paskyros paruošimą',
        ],
    ],
    'completion' => [
        'feedback' => 'Paskyros paruošimas baigtas.',
    ],
    'states' => [
        'saving' => 'Saugiai išsaugoma…',
        'checking' => 'Tikrinamas jūsų ryšys su augintiniu…',
        'offline' => 'Nesate prisijungę prie interneto. Prisijunkite prieš išsaugodami šį žingsnį.',
        'unsaved' => 'Šiame žingsnyje yra neišsaugotų pakeitimų.',
    ],
    'validation' => [
        'summary' => 'Prieš tęsdami peržiūrėkite pažymėtą informaciją.',
        'acknowledgement' => 'Patvirtinkite, kad suprantate: šiuos pasirinkimus vėliau galėsite peržiūrėti.',
        'privacy_acknowledgement' => 'Prieš baigdami paskyros paruošimą patvirtinkite, kad suprantate privatumo ribas.',
        'pet_choice' => 'Pasirinkite vieną iš galimų ryšio su augintiniu variantų.',
        'pet_evidence' => 'Kol kas nepavyko patvirtinti šio ryšio su augintiniu jūsų paskyrai.',
    ],
    'errors' => [
        'state_unavailable' => 'Nepavyko rasti išsaugotos paskyros paruošimo eigos. Atnaujinkite puslapį ir bandykite dar kartą.',
        'stale_state' => 'Ši paskyros paruošimo eiga pasikeitė kitame skirtuke arba užklausoje. Prieš tęsdami atnaujinkite puslapį.',
        'transition_conflict' => 'Šis paskyros paruošimo žingsnis dar nepasiekiamas.',
    ],
    'middleware' => [
        'incomplete_detail' => 'Prieš pasiekdami šį išteklių užbaikite paskyros paruošimą.',
    ],
    'accessibility' => [
        'skip_to_content' => 'Pereiti prie paskyros paruošimo',
    ],
];
