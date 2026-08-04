<?php

declare(strict_types=1);

return [
    'page' => [
        'title' => 'Kaimynai | PawCircle',
        'eyebrow' => 'Portlando kaimynai',
        'heading' => 'Susipažinkite su žmonėmis, kurie rūpinasi augintiniais',
        'description' => 'Raskite netoliese gyvenančius šeimininkus, kurių maršrutai, rutina ir požiūris į augintinių priežiūrą sutampa su jūsų.',
        'count' => '4 žmonės · Portlandas, Oregonas',
    ],
    'actions' => [
        'new_message' => 'Nauja žinutė',
    ],
    'summary' => [
        'label' => 'Kaimynų santrauka',
        'unavailable' => 'Kaimynų santrauka šiuo metu nepasiekiama.',
        'closest' => [
            'label' => 'Arčiausiai',
            'value' => 'už 0,8 myl.',
            'detail' => 'Pearl District',
        ],
        'circles' => [
            'label' => 'Bendri rateliai',
            'value' => '7 ryšiai',
            'detail' => 'Visoje „PawCircle“ bendruomenėje',
        ],
        'pets' => [
            'label' => 'Augintiniai',
            'value' => '4 augintiniai',
            'detail' => 'Šunys, katės ir triušiai',
        ],
    ],
    'filters' => [
        'toolbar_label' => 'Kaimynų filtrai',
        'category_label' => 'Kaimynų kategorijų filtrai',
        'recommended' => 'Rekomenduojami',
        'dog_people' => 'Šunų mylėtojai',
        'cat_people' => 'Kačių mylėtojai',
        'foster_network' => 'Laikinos globos tinklas',
    ],
    'sort' => [
        'label' => 'Kaimynų rikiavimo tvarka',
        'closest' => 'Pirmiausia arčiausi',
        'name' => 'Pagal vardą',
    ],
    'search' => [
        'label' => 'Ieškoti kaimynų',
        'placeholder' => 'Ieškokite pagal žmogų, augintinį ar rajoną',
    ],
    'results' => [
        'title' => 'Netoliese esantys žmonės',
        'empty_title' => 'Šių filtrų neatitinka nė vienas kaimynas',
        'empty_description' => 'Išplėskite žmogaus, augintinio ar rajono paiešką.',
    ],
    'card' => [
        'empty_interests' => 'Domina nauji augintinių rateliai',
        'brand_initials' => 'PC',
        'follow' => 'Sekti',
        'following' => 'Sekama',
    ],
    'catalog' => [
        'ari' => [
            'name' => 'Ari Jensen',
            'category' => 'Pasivaikščiojimai su šunimis',
            'neighborhood' => 'Pearl District',
            'distance' => 'už 0,8 myl.',
            'pet' => 'Mochi · Šibos mišrūnas',
            'status' => 'Kviečia į ramius pasivaikščiojimus prie kavinių',
            'image_alt' => 'Ari su Mochi ilsisi rajono parke',
            'interests' => [
                'first' => 'Miesto pasivaikščiojimai',
                'second' => 'Dresūra',
            ],
        ],
        'noah' => [
            'name' => 'Noah Patel',
            'category' => 'Vyresnių augintinių priežiūra',
            'neighborhood' => 'Sellwood',
            'distance' => 'už 1,7 myl.',
            'pet' => 'Juniper · Vyresnis retriveris',
            'status' => 'Dažniausiai vaikšto prieš saulėlydį',
            'image_alt' => 'Noah treniruojasi su mažu šunimi miškingame parke',
            'interests' => [
                'first' => 'Vyresni augintiniai',
                'second' => 'Pavėsingi maršrutai',
            ],
        ],
        'lena' => [
            'name' => 'Lena Brooks',
            'category' => 'Kačių mylėtojai',
            'neighborhood' => 'Alberta Arts',
            'distance' => 'už 2,1 myl.',
            'pet' => 'Pip · Naminė trumpaplaukė katė',
            'status' => 'Dalijasi laikinos globos įrengimo užrašais',
            'image_alt' => 'Lena namuose laiko baltą kačiuką',
            'interests' => [
                'first' => 'Kačių priežiūra',
                'second' => 'Laikina globa',
            ],
        ],
        'priya' => [
            'name' => 'Priya Shah',
            'category' => 'Smulkūs augintiniai',
            'neighborhood' => 'St. Johns',
            'distance' => 'už 3,8 myl.',
            'pet' => 'Clover · Mini Lop mišrūnas',
            'status' => 'Sodo rutina ir rami priežiūra',
            'image_alt' => 'Priya patalpoje laiko dėmėtą triušį',
            'interests' => [
                'first' => 'Triušiai',
                'second' => 'Laikas sode',
            ],
        ],
    ],
    'profile' => [
        'page' => [
            'title' => 'Ari Jensen | „PawCircle“',
            'back' => 'Grįžti prie kaimynų',
            'actions_label' => 'Veiksmai su :name profiliu',
        ],
        'hero' => [
            'summary_label' => 'Kaimyno profilio santrauka',
            'summary_unavailable' => 'Kaimyno profilio santrauka šiuo metu nepasiekiama.',
        ],
        'sections' => [
            'about' => [
                'eyebrow' => 'Rajono kasdienybė',
                'title' => 'Apie Ari',
            ],
            'interests' => [
                'title' => 'Bendri pomėgiai',
                'empty' => 'Bendrų pomėgių dar nėra.',
            ],
            'mutual_neighbors' => [
                'title' => 'Bendri kaimynai',
                'count' => '{0} Bendrų kaimynų nėra|{1} :count bendras kaimynas|[2,9] :count bendri kaimynai|[10,*] :count bendrų kaimynų',
                'empty' => 'Bendrų kaimynų dar nėra.',
            ],
            'communities' => [
                'title' => 'Bendruomenės',
                'empty' => 'Dar neprisijungta prie nė vienos bendruomenės.',
            ],
            'moments' => [
                'eyebrow' => 'Ari ir Mochi akimirkos',
                'title' => 'Naujausios akimirkos',
                'empty' => 'Akimirkų dar nepasidalyta.',
            ],
        ],
        'actions' => [
            'follow' => 'Sekti',
            'following' => 'Sekama',
            'message' => 'Rašyti žinutę',
            'plan_walk' => 'Planuoti pasivaikščiojimą',
        ],
        'identity' => [
            'name' => 'Ari Jensen',
            'handle' => '@ari-jensen',
            'category' => 'Pasivaikščiojimai su šunimis',
            'location' => 'Pearl District, Portlandas, Oregonas',
            'neighborhood' => 'Pearl rajonas',
            'distance' => 'už 0,8 myl.',
            'member_since' => 'Narys nuo 2024 m.',
            'status' => 'Kviečia į ramius pasivaikščiojimus prie kavinių',
            'bio' => 'Ari ir Mochi nuolat vaikšto ramiomis Pearl District gatvėmis, pavėsinguose parkuose ir kantriai pratinasi prie kavinių. Jie mielai dalijasi neskubriomis miesto rutinomis su netoliese gyvenančiais augintinių šeimininkais.',
            'avatar_alt' => 'Ari su Mochi ilsisi rajono parke',
            'cover_image_alt' => 'Du pasivaikščioti pasiruošę šiba inu šunys',
        ],
        'stats' => [
            'pet' => [
                'label' => 'Augintinis',
                'detail' => 'Šibos mišrūnas',
            ],
            'mutuals' => [
                'label' => 'Bendri ryšiai',
                'detail' => 'Netoliese gyvenantys kaimynai',
            ],
            'home' => [
                'label' => 'Namai',
                'value' => 'Pearl rajonas',
                'detail' => 'už 0,8 myl.',
            ],
        ],
        'interests' => [
            'city_walks' => 'Miesto pasivaikščiojimai',
            'training' => 'Dresūra',
            'quiet_patios' => 'Ramios terasos',
            'urban_routines' => 'Miesto rutina',
        ],
        'pet' => [
            'name' => 'Mochi',
            'owner_name' => 'Ari',
            'breed' => 'Šibos mišrūnas',
            'age' => '3 metai',
            'status' => 'Ramiai jaučiasi pažįstamose vietose ir geriausiai atsipalaiduoja, kai pažintys vyksta kantriai.',
            'image_alt' => 'Mochi sėdi su kitu šiba šunimi rajono kavinėje',
            'lives_with' => 'Gyvena su :owner',
            'traits_empty' => 'Rutinos ypatybės šiuo metu nepasiekiamos.',
            'routine_empty' => 'Rutinos informacijos dar nėra.',
            'traits' => [
                'patient_hellos' => 'Kantrios pažintys',
                'city_confident' => 'Drąsus mieste',
                'treat_motivated' => 'Motyvuoja skanėstai',
            ],
            'routine' => [
                'route_label' => 'Mėgstamas maršrutas',
                'route_value' => 'NW 11th–Fields Park',
                'time_label' => 'Geriausias laikas',
                'time_value' => 'Ankstyvas rytas',
                'cafe_label' => 'Kavinės taisyklė',
                'cafe_value' => 'Pirmiausia terasa, tada staliukas',
            ],
        ],
        'mutual_neighbors' => [
            'mia' => [
                'name' => 'Mia Carter',
                'context' => 'Pasivaikščiojimai Richmond rajone',
            ],
            'jamie' => [
                'name' => 'Jamie Cho',
                'context' => '„Apartment Pets PDX“ bendruomenė',
            ],
            'noah' => [
                'name' => 'Noah Patel',
                'context' => '„Trail Tails“ bendruomenė',
            ],
            'lena' => [
                'name' => 'Lena Brooks',
                'context' => '„Foster Network PDX“ bendruomenė',
            ],
        ],
        'communities' => [
            'apartment_pets' => [
                'name' => 'Apartment Pets PDX',
                'topic' => 'Mažų erdvių rutina',
                'members' => '2,4 tūkst. narių',
            ],
            'trail_tails' => [
                'name' => 'Trail Tails',
                'topic' => 'Savaitgalio maršrutai mieste',
                'members' => '8,1 tūkst. narių',
            ],
        ],
        'moments' => [
            'first' => [
                'author' => 'Ari Jensen',
                'pet' => 'Mochi',
                'time' => 'prieš 18 min.',
                'body' => 'Mochi pagaliau perėjo visą kavinės terasą neskubėdamas pasisveikinti. Padėjo ramūs kampeliai ir kišenė skanėstų.',
                'image_alt' => 'Mochi eina šalia kito šuns medžiais apsodintu taku',
                'first_tag' => 'Dresūra',
                'second_tag' => 'Miesto pasivaikščiojimai',
            ],
            'second' => [
                'author' => 'Ari Jensen',
                'pet' => 'Mochi',
                'time' => 'prieš 3 dienas',
                'body' => 'Išbandėme ramų kampelį rajono kavinėje prieš rytinį lankytojų antplūdį. Lėtai apėjęs terasą Mochi patogiai įsitaisė.',
                'image_alt' => 'Mochi sėdi su kitu šiba šunimi rajono kavinėje',
                'first_tag' => 'Kavinės rutina',
                'second_tag' => 'Ramios pažintys',
            ],
        ],
    ],
];
