<?php

declare(strict_types=1);

return [
    'page' => [
        'title' => 'Соседи | PawCircle',
        'eyebrow' => 'Соседи в Портленде',
        'heading' => 'Познакомьтесь с людьми, которые заботятся о питомцах',
        'description' => 'Найдите поблизости хозяев с похожими маршрутами, распорядком и подходом к заботе о питомцах.',
        'count' => '4 человека · Портленд, Орегон',
    ],
    'actions' => [
        'new_message' => 'Новое сообщение',
    ],
    'summary' => [
        'label' => 'Кратко о соседях',
        'unavailable' => 'Сводка о соседях сейчас недоступна.',
        'closest' => [
            'label' => 'Ближе всего',
            'value' => 'в 0,8 мили',
            'detail' => 'Pearl District',
        ],
        'circles' => [
            'label' => 'Общие круги',
            'value' => '7 связей',
            'detail' => 'Во всём сообществе PawCircle',
        ],
        'pets' => [
            'label' => 'Питомцы',
            'value' => '4 питомца',
            'detail' => 'Собаки, кошки и кролики',
        ],
    ],
    'filters' => [
        'toolbar_label' => 'Фильтры соседей',
        'category_label' => 'Фильтры по категориям соседей',
        'recommended' => 'Рекомендованные',
        'dog_people' => 'Любители собак',
        'cat_people' => 'Любители кошек',
        'foster_network' => 'Сеть временной опеки',
    ],
    'sort' => [
        'label' => 'Порядок сортировки соседей',
        'closest' => 'Сначала ближайшие',
        'name' => 'По имени',
    ],
    'search' => [
        'label' => 'Искать соседей',
        'placeholder' => 'Поиск по человеку, питомцу или району',
    ],
    'results' => [
        'title' => 'Люди поблизости',
        'empty_title' => 'Нет соседей, подходящих под эти фильтры',
        'empty_description' => 'Расширьте поиск по человеку, питомцу или району.',
    ],
    'card' => [
        'empty_interests' => 'Готовы к новым кругам владельцев питомцев',
        'brand_initials' => 'PC',
        'follow' => 'Подписаться',
        'following' => 'Вы подписаны',
    ],
    'catalog' => [
        'ari' => [
            'name' => 'Ari Jensen',
            'category' => 'Прогулки с собаками',
            'neighborhood' => 'Pearl District',
            'distance' => 'в 0,8 мили',
            'pet' => 'Mochi · Метис сиба-ину',
            'status' => 'Предпочитает спокойные прогулки у кафе',
            'image_alt' => 'Ari и Mochi отдыхают в районном парке',
            'interests' => [
                'first' => 'Прогулки по городу',
                'second' => 'Дрессировка',
            ],
        ],
        'noah' => [
            'name' => 'Noah Patel',
            'category' => 'Забота о пожилых питомцах',
            'neighborhood' => 'Sellwood',
            'distance' => 'в 1,7 мили',
            'pet' => 'Juniper · Пожилой ретривер',
            'status' => 'Обычно гуляет перед закатом',
            'image_alt' => 'Noah занимается с небольшой собакой в лесном парке',
            'interests' => [
                'first' => 'Пожилые питомцы',
                'second' => 'Тенистые маршруты',
            ],
        ],
        'lena' => [
            'name' => 'Lena Brooks',
            'category' => 'Любители кошек',
            'neighborhood' => 'Alberta Arts',
            'distance' => 'в 2,1 мили',
            'pet' => 'Pip · Домашняя короткошёрстная кошка',
            'status' => 'Делится заметками об обустройстве временной опеки',
            'image_alt' => 'Lena держит дома белого котёнка',
            'interests' => [
                'first' => 'Уход за кошками',
                'second' => 'Временная опека',
            ],
        ],
        'priya' => [
            'name' => 'Priya Shah',
            'category' => 'Маленькие питомцы',
            'neighborhood' => 'St. Johns',
            'distance' => 'в 3,8 мили',
            'pet' => 'Clover · Метис мини-лопа',
            'status' => 'Садовый распорядок и спокойный уход',
            'image_alt' => 'Priya держит в помещении пятнистого кролика',
            'interests' => [
                'first' => 'Кролики',
                'second' => 'Время в саду',
            ],
        ],
    ],
    'profile' => [
        'page' => [
            'title' => 'Профиль Ari Jensen | PawCircle',
            'back' => 'Назад к соседям',
            'actions_label' => 'Действия с профилем :name',
        ],
        'hero' => [
            'summary_label' => 'Кратко о профиле соседа',
            'summary_unavailable' => 'Сводка профиля соседа сейчас недоступна.',
        ],
        'sections' => [
            'about' => [
                'eyebrow' => 'Жизнь по соседству',
                'title' => 'Об Ari',
            ],
            'interests' => [
                'title' => 'Общие интересы',
                'empty' => 'Общих интересов пока нет.',
            ],
            'mutual_neighbors' => [
                'title' => 'Общие соседи',
                'count' => '{0} Нет общих соседей|{1} :count общий сосед|[2,4] :count общих соседа|[5,*] :count общих соседей',
                'empty' => 'Общих соседей пока нет.',
            ],
            'communities' => [
                'title' => 'Сообщества',
                'empty' => 'Участия в сообществах пока нет.',
            ],
            'moments' => [
                'eyebrow' => 'Моменты Ari и Mochi',
                'title' => 'Недавние моменты',
                'empty' => 'Здесь пока нет опубликованных моментов.',
            ],
        ],
        'actions' => [
            'follow' => 'Подписаться',
            'following' => 'Вы подписаны',
            'message' => 'Написать',
            'plan_walk' => 'Запланировать прогулку',
        ],
        'identity' => [
            'name' => 'Ari Jensen',
            'handle' => '@ari-jensen',
            'category' => 'Прогулки с собаками',
            'location' => 'Pearl District, Портленд, Орегон',
            'neighborhood' => 'район Pearl',
            'distance' => 'в 0,8 мили',
            'member_since' => 'В сообществе с 2024 года',
            'status' => 'Предпочитает спокойные прогулки у кафе',
            'bio' => 'Ari и Mochi придерживаются привычного маршрута по тихим улицам Pearl District, тенистым паркам и кафе, где можно знакомиться без спешки. Они всегда рады обменяться спокойными городскими маршрутами с хозяевами питомцев поблизости.',
            'avatar_alt' => 'Ari и Mochi отдыхают в районном парке',
            'cover_image_alt' => 'Две собаки породы сиба-ину готовы к прогулке по району',
        ],
        'stats' => [
            'pet' => [
                'label' => 'Питомец',
                'detail' => 'Метис сиба-ину',
            ],
            'mutuals' => [
                'label' => 'Общие связи',
                'detail' => 'Соседи поблизости',
            ],
            'home' => [
                'label' => 'Дом',
                'value' => 'район Pearl',
                'detail' => 'в 0,8 мили',
            ],
        ],
        'interests' => [
            'city_walks' => 'Прогулки по городу',
            'training' => 'Дрессировка',
            'quiet_patios' => 'Тихие террасы',
            'urban_routines' => 'Городской распорядок',
        ],
        'pet' => [
            'name' => 'Mochi',
            'owner_name' => 'Ari',
            'breed' => 'Метис сиба-ину',
            'age' => '3 года',
            'status' => 'Спокоен в знакомых местах и лучше всего чувствует себя при неторопливом знакомстве.',
            'image_alt' => 'Mochi сидит с другой сиба-ину в районном кафе',
            'lives_with' => 'Живёт с :owner',
            'traits_empty' => 'Особенности распорядка сейчас недоступны.',
            'routine_empty' => 'Сведений о распорядке пока нет.',
            'traits' => [
                'patient_hellos' => 'Спокойные знакомства',
                'city_confident' => 'Уверенно чувствует себя в городе',
                'treat_motivated' => 'Любит поощрение лакомствами',
            ],
            'routine' => [
                'route_label' => 'Любимый маршрут',
                'route_value' => 'от NW 11th до Fields Park',
                'time_label' => 'Лучшее время',
                'time_value' => 'Раннее утро',
                'cafe_label' => 'Правило для кафе',
                'cafe_value' => 'Сначала терраса, затем столик',
            ],
        ],
        'mutual_neighbors' => [
            'mia' => [
                'name' => 'Mia Carter',
                'context' => 'Прогулки в Richmond',
            ],
            'jamie' => [
                'name' => 'Jamie Cho',
                'context' => 'Сообщество Apartment Pets PDX',
            ],
            'noah' => [
                'name' => 'Noah Patel',
                'context' => 'Сообщество Trail Tails',
            ],
            'lena' => [
                'name' => 'Lena Brooks',
                'context' => 'Сообщество Foster Network PDX',
            ],
        ],
        'communities' => [
            'apartment_pets' => [
                'name' => 'Apartment Pets PDX',
                'topic' => 'Распорядок в небольшом пространстве',
                'members' => '2,4 тыс. участников',
            ],
            'trail_tails' => [
                'name' => 'Trail Tails',
                'topic' => 'Городские маршруты выходного дня',
                'members' => '8,1 тыс. участников',
            ],
        ],
        'moments' => [
            'first' => [
                'author' => 'Ari Jensen',
                'pet' => 'Mochi',
                'time' => '18 мин. назад',
                'body' => 'Mochi наконец прошёл через всю террасу кафе и ни разу не рванул знакомиться. Помогли тихие уголки и карман с лакомствами.',
                'image_alt' => 'Mochi идёт рядом с другой собакой по обсаженной деревьями дорожке',
                'first_tag' => 'Дрессировка',
                'second_tag' => 'Прогулки по городу',
            ],
            'second' => [
                'author' => 'Ari Jensen',
                'pet' => 'Mochi',
                'time' => '3 дня назад',
                'body' => 'Мы заняли тихий уголок районного кафе до утреннего наплыва гостей. После одного медленного круга по террасе Mochi спокойно устроился рядом.',
                'image_alt' => 'Mochi сидит с другой сиба-ину в районном кафе',
                'first_tag' => 'Распорядок в кафе',
                'second_tag' => 'Спокойные знакомства',
            ],
        ],
    ],
];
