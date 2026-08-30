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
        'completed' => 'Baigta žingsnių: :completed iš :total',
        'status' => [
            'complete' => 'Baigta',
            'current' => 'Dabartinis žingsnis',
            'upcoming' => 'Nepradėta',
        ],
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
            'locale_help' => 'Sąsajos kalbą vėliau galėsite pakeisti profilio nustatymuose.',
            'timezone_help' => 'Naudojama tvarkaraščiams, priminimams, įvykiams ir rodomam laikui. Ji neatskleidžia tikslios jūsų vietos.',
            'save' => 'Išsaugoti ir tęsti',
        ],
        'pet_relationship' => [
            'label' => 'Ryšys su augintiniu',
            'title' => 'Susiekite augintinį, kai būsite pasirengę',
            'body' => 'Galite sukurti privatų augintinio profilį, rasti esamą profilį ir paprašyti prieigos arba tęsti be augintinio. Ši galimybė liks prieinama ir vėliau.',
            'legend' => 'Pasirinkite savo ryšį su augintiniu',
            'create_or_find' => 'Sukurti arba rasti augintinio profilį',
            'managed_pet' => [
                'label' => 'Turiu arba valdau augintinį',
                'description' => 'Pridėkite naują augintinį arba tęskite su jau aktyviai valdomu augintiniu.',
            ],
            'access_requested' => [
                'label' => 'Padedu prižiūrėti esamą augintinį',
                'description' => 'Raskite augintinį ir paprašykite tinkamos prieigos. Kol prašymas tikrinamas, jis prieigos nesuteikia.',
            ],
            'no_pet' => [
                'label' => 'Šiuo metu augintinio neturiu',
                'description' => 'Vis tiek galite naudotis „PawCircle“ ir augintinį pridėti vėliau.',
            ],
            'add_later' => [
                'label' => 'Augintinį pridėsiu vėliau',
                'description' => 'Užbaikite paruošimą dabar, o profilį sukurkite arba susiekite, kai būsite pasirengę.',
            ],
            'not_now' => 'Kol kas tęsti be augintinio',
            'managed_summary' => 'Aktyviai valdomi augintiniai',
            'managed_summary_more' => 'Baigę paruošimą augintinių darbo srityje rasite daugiau aktyviai valdomų augintinių.',
            'managed_evidence_private' => 'Aktyvus ryšys su augintiniu patvirtintas. Duomenys nerodomi, nes dabartinė prieiga neleidžia šios peržiūros.',
            'managed_empty' => 'Aktyvus ryšys su augintiniu dar nepatvirtintas. Sukurkite profilį arba pasirinkite kitą teisingą variantą.',
            'access_pending' => 'Jūsų prieigos prašymas laukia patikros. Galite tęsti paruošimą, tačiau prašymas nesuteikia prieigos, kol nepatvirtintas.',
            'invitation_pending' => 'Laukiama jūsų sprendimo dėl kvietimo valdyti augintinį. Kol kvietimas nepriimtas, tai nėra aktyvus valdymas.',
            'inactive_relationship' => 'Ankstesnis ryšys su augintiniu nebegalioja.',
            'edit' => 'Grįžti prie ryšio su augintiniu',
            'continue' => 'Išsaugoti ir tęsti',
        ],
        'privacy_discovery' => [
            'label' => 'Privatumas',
            'title' => 'Patvirtinkite paieškos ir kontakto pasirinkimus',
            'body' => 'Iš pradžių visos parinktys išjungtos. Įjunkite tik tuos būdus, kuriais norite būti randami ar pasiekiami kitų narių.',
            'options_legend' => 'Paieškos ir kontakto parinktys',
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
        'progress_updated' => 'Paskyros paruošimo eiga pasikeitė kitame skirtuke. Perkėlėme jus į dabartinį žingsnį.',
    ],
    'validation' => [
        'summary' => 'Prieš tęsdami peržiūrėkite pažymėtą informaciją.',
        'acknowledgement' => 'Patvirtinkite, kad suprantate: šiuos pasirinkimus vėliau galėsite peržiūrėti.',
        'privacy_acknowledgement' => 'Prieš baigdami paskyros paruošimą patvirtinkite, kad suprantate privatumo ribas.',
        'pet_choice' => 'Pasirinkite vieną iš galimų ryšio su augintiniu variantų.',
        'pet_evidence' => 'Kol kas nepavyko patvirtinti šio ryšio su augintiniu jūsų paskyrai.',
        'locale' => 'Pasirinkite anglų, lietuvių arba rusų kalbą.',
        'timezone' => 'Pasirinkite galiojančią IANA laiko juostą.',
        'privacy_choice' => 'Pasirinkite galiojančią privatumo parinktį.',
    ],
    'errors' => [
        'state_unavailable' => 'Nepavyko rasti išsaugotos paskyros paruošimo eigos. Atnaujinkite puslapį ir bandykite dar kartą.',
        'stale_state' => 'Ši paskyros paruošimo eiga pasikeitė kitame skirtuke arba užklausoje. Prieš tęsdami atnaujinkite puslapį.',
        'transition_conflict' => 'Šis paskyros paruošimo žingsnis dar nepasiekiamas.',
        'pet_evidence_current' => 'Šis ryšys su augintiniu vis dar galioja, todėl jo negalima pakeisti atkūrimo veiksmu.',
    ],
    'middleware' => [
        'incomplete_detail' => 'Prieš pasiekdami šį išteklių užbaikite paskyros paruošimą.',
    ],
    'accessibility' => [
        'skip_to_content' => 'Pereiti prie paskyros paruošimo',
    ],
];
