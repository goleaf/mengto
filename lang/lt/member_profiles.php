<?php

return [
    'page' => [
        'browser_title' => ':name | PawCircle',
        'eyebrow' => 'Bendruomenės narys',
        'description' => 'Čia rodoma tik vieša profilio informacija ir jums prieinami įrašai.',
        'public_status' => 'Atrasti galimas profilis',
    ],
    'actions' => [
        'back_to_discovery' => 'Grįžti į narių paiešką',
    ],
    'details' => [
        'eyebrow' => 'Profilio apimtis',
        'title' => 'Vieša informacija',
        'member_type' => 'Profilio tipas',
        'joined' => 'Narys nuo',
    ],
    'posts' => [
        'eyebrow' => 'Matoma jums',
        'title' => 'Naujausi įrašai',
        'empty_title' => 'Matomų įrašų nėra',
        'empty_description' => 'Šis narys neturi jūsų auditorijai prieinamų dabartinių įrašų.',
    ],
    'pets' => [
        'eyebrow' => 'Vieši profiliai',
        'title' => 'Augintiniai',
        'empty' => 'Viešų augintinių profilių nėra.',
    ],
    'errors' => [
        'invalid_actor' => 'Nario profiliui reikalinga naudotojo tapatybė.',
        'demo_seed_environment' => 'Atradimų demonstracinius duomenis galima kurti tik leidžiamoje aplinkoje.',
    ],
    'owner' => [
        'page' => [
            'title' => ':name :handle profilis | „PawCircle“',
        ],
        'hero' => [
            'summary_label' => 'Šeimininko profilio santrauka',
            'summary_unavailable' => 'Šeimininko profilio santrauka šiuo metu nepasiekiama.',
            'actions_label' => 'Veiksmai su :name profiliu',
        ],
        'tabs' => [
            'label' => 'Šeimininko profilio skyriai',
            'overview' => 'Apžvalga',
            'pets' => 'Augintiniai',
            'posts' => 'Įrašai',
            'about' => 'Apie',
        ],
        'preview' => [
            'title' => 'Matomumo peržiūra',
            'label' => 'Peržiūrėti profilį kaip',
            'options' => [
                'owner' => 'Šeimininkas',
                'public' => 'Viešas lankytojas',
                'follower' => 'Sekėjas',
                'friend' => 'Draugas',
            ],
            'audiences' => [
                'owner' => 'Matote visą šeimininko profilį.',
                'public' => 'Matote profilį kaip viešas lankytojas.',
                'follower' => 'Matote profilį kaip sekėjas.',
                'friend' => 'Matote profilį kaip draugas.',
            ],
        ],
        'sections' => [
            'about' => [
                'eyebrow' => 'Rajono kasdienybė',
                'title' => 'Apie Mia',
            ],
            'pets' => [
                'eyebrow' => 'Mia namuose',
                'title' => 'Scout, Nori ir šeima',
                'tab_eyebrow' => 'Atskiri socialiniai profiliai',
                'tab_title' => 'Mia augintiniai',
                'empty' => 'Augintinių profilių nėra.',
                'add' => 'Pridėti augintinį',
            ],
            'posts' => [
                'eyebrow' => 'Mia akimirkos',
                'tab_eyebrow' => 'Paskelbė Mia',
                'title' => 'Naujausios akimirkos',
                'tab_title' => 'Šeimininko įrašai',
                'empty' => 'Akimirkų dar nepasidalyta.',
            ],
            'details' => [
                'eyebrow' => 'Vieša tapatybė',
                'title' => 'Profilio informacija',
            ],
            'interests' => [
                'eyebrow' => 'Bendri pomėgiai',
                'title' => 'Pomėgiai',
                'empty' => 'Pomėgiais dar nepasidalyta.',
            ],
            'languages' => [
                'eyebrow' => 'Pokalbiai',
                'title' => 'Kalbos',
            ],
            'privacy' => [
                'eyebrow' => 'Auditorijos valdymas',
                'title' => 'Privatumo santrauka',
            ],
            'completion' => [
                'eyebrow' => 'Profilio pagrindai',
                'title' => 'Profilio parengtis',
            ],
            'badges' => [
                'eyebrow' => 'Patikimumo ženklai',
                'title' => 'Ženkleliai',
            ],
            'availability' => [
                'eyebrow' => 'Pasivaikščiojimų profilis',
                'title' => 'Prieinamumas',
            ],
            'safety' => [
                'eyebrow' => 'Jūsų ribos',
                'title' => 'Saugumo valdikliai',
                'description' => 'Blokavimas yra abipusis. Pranešimai privatūs, o profilis apie juos neinformuojamas.',
                'actions_label' => 'Profilio saugumo veiksmai',
            ],
        ],
        'restrictions' => [
            'pets' => [
                'title' => 'Augintinių profiliai privatūs',
                'tab_description' => 'Mia šį sąrašą rodo tik pasirinktai auditorijai.',
                'overview_description' => 'Ši auditorija negali matyti Mia augintinių sąrašo.',
            ],
            'posts' => [
                'title' => 'Įrašų matomumas ribotas',
                'tab_description' => 'Sekite Mia arba užmegzkite ryšį, kad matytumėte artimesnei auditorijai skirtus įrašus.',
                'overview_title' => 'Šeimininko įrašų matomumas ribotas',
                'overview_description' => 'Mia šiomis akimirkomis dalijasi tik su artimesne auditorija.',
            ],
        ],
        'identity' => [
            'name' => 'Mia Carter',
            'handle' => '@mia-carter',
            'location' => 'Richmond, Portlandas, Oregonas',
            'private_location' => 'Vieta neviešinama',
            'avatar_alt' => 'Mia Carter šypsosi lauke',
            'summary' => 'Savaitgalių žygeivė, laikinos globos savanorė ir dviejų labai skirtingų augintinių rutinų šeimininkė.',
            'media_label' => 'Atverti Mia Carter profilį',
            'role' => 'Augintinių šeimininkė ir laikinos globos savanorė',
            'member_since' => 'Narė nuo 2024 m.',
            'status' => 'Kviečia į savaitgalio pasivaikščiojimus',
            'bio' => 'Mia planuoja ramius pasivaikščiojimus po rajoną, dalijasi laikinos globos rutina ir pažintis derina prie kiekvieno augintinio tempo.',
            'cover_image_alt' => 'Scout guli žolėje už teniso kamuoliuko',
        ],
        'stats' => [
            'pets' => [
                'label' => 'Augintiniai',
                'detail' => 'Atskiri profiliai',
            ],
            'followers' => [
                'label' => 'Sekėjai',
                'detail' => 'Šeimininko auditorija',
            ],
            'following' => [
                'label' => 'Sekami',
                'detail' => 'Žmonės ir augintiniai',
            ],
            'posts' => [
                'label' => 'Įrašai',
                'detail' => 'Mia publikacijos',
            ],
        ],
        'actions' => [
            'edit' => 'Redaguoti profilį',
            'settings' => 'Nustatymai',
            'privacy' => 'Privatumas',
            'share' => 'Bendrinti',
            'profile_label' => 'Mia Carter profilis',
            'follow' => 'Sekti Mia',
            'following' => 'Mia sekama',
            'friend' => 'Pridėti draugę',
            'request_sent' => 'Kvietimas išsiųstas',
            'message' => 'Rašyti žinutę',
            'block' => 'Blokuoti profilį',
            'unblock' => 'Atblokuoti profilį',
            'report' => 'Pranešti apie profilį',
        ],
        'availability' => [
            'time_label' => 'Geriausias laikas',
            'time_value' => 'Savaitgalio rytai',
            'pace_label' => 'Įprastas tempas',
            'pace_value' => 'Lengvas arba vidutinis',
            'home_label' => 'Namų rajonas',
            'home_value' => 'Richmond, Portlandas',
            'private_value' => 'Privatu',
        ],
        'interests' => [
            'trail_walks' => 'Pasivaikščiojimai takais',
            'foster_care' => 'Laikina globa',
            'cat_enrichment' => 'Kačių užimtumas',
            'quiet_parks' => 'Ramūs parkai',
            'positive_training' => 'Pozityvi dresūra',
        ],
        'languages' => [
            'english' => [
                'title' => 'Anglų kalba',
                'description' => 'Pagrindinė profilio ir pokalbių kalba',
            ],
            'spanish' => [
                'title' => 'Ispanų kalba',
                'description' => 'Galima susirašinėti kasdienėmis temomis',
            ],
        ],
        'details' => [
            'username' => 'Naudotojo vardas',
            'account_type' => 'Paskyros tipas',
            'account_type_value' => 'Augintinių šeimininkė ir savanorė',
            'joined' => 'Prisijungė',
            'joined_value' => '2024',
            'language' => 'Profilio kalba',
            'language_value' => 'Anglų kalba',
        ],
        'badges' => [
            'email_verified' => 'El. paštas patvirtintas',
            'active_volunteer' => 'Aktyvi savanorė',
            'profile_complete' => 'Profilis užpildytas',
        ],
        'completion' => [
            'label' => 'Profilio užpildymas',
            'detail' => 'Pridėkite nebūtiną svetainės nuorodą ir užbaikite viešą profilio pagrindą.',
        ],
        'privacy' => [
            'labels' => [
                'location' => 'Vieta',
                'pets' => 'Augintinių profiliai',
                'posts' => 'Įrašai',
                'friends' => 'Draugai',
                'activity' => 'Veikla',
                'care' => 'Priežiūros informacija',
            ],
            'values' => [
                'public' => 'Visiems',
                'members' => 'Registruotiems nariams',
                'followers' => 'Sekėjams',
                'friends' => 'Draugams',
                'owners' => 'Šeimininkams ir valdytojams',
                'hidden' => 'Paslėpta',
            ],
        ],
    ],
];
