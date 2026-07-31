<?php

declare(strict_types=1);

$dimensions = [
    'helpfulness' => 'Полезность',
    'answer-quality' => 'Качество ответов',
    'reliability' => 'Надёжность',
    'evidence-quality' => 'Качество доказательств',
    'empathy' => 'Эмпатия',
    'respectful-communication' => 'Уважительное общение',
    'community-support' => 'Поддержка сообщества',
    'species-experience' => 'Опыт по конкретному виду',
    'category-expertise' => 'Экспертиза в категории',
    'local-knowledge' => 'Местные знания',
    'rescue-contribution' => 'Вклад в спасение',
    'lost-found-contribution' => 'Вклад в поиск животных',
    'adoption-support' => 'Поддержка пристройства',
    'mentoring' => 'Наставничество',
    'guide-contribution' => 'Вклад в руководства',
    'correction-contribution' => 'Вклад в исправления',
    'moderation-contribution' => 'Вклад в модерацию',
    'marketplace-trust' => 'Доверие на площадке объявлений',
    'service-review-reliability' => 'Надёжность отзывов об услугах',
    'event-reliability' => 'Надёжность мероприятий',
    'professional-contribution' => 'Профессиональный вклад',
];
$trustLevels = [
    'new-member' => 'Новый участник',
    'member' => 'Участник',
    'established-member' => 'Постоянный участник',
    'trusted-contributor' => 'Надёжный автор',
    'mentor' => 'Наставник',
    'community-reviewer' => 'Рецензент сообщества',
    'category-steward' => 'Куратор категории',
    'moderator' => 'Модератор',
    'senior-moderator' => 'Старший модератор',
    'verified-professional' => 'Проверенный специалист',
    'organization-representative' => 'Представитель организации',
    'administrator' => 'Администратор',
];
$badges = [
    'onboarding' => 'Знакомство завершено',
    'helpful-contributor' => 'Полезный автор',
    'detailed-answer' => 'Подробный ответ',
    'evidence-contributor' => 'Автор доказательств',
    'guide-author' => 'Автор руководства',
    'guide-reviewer' => 'Рецензент руководств',
    'translator' => 'Переводчик',
    'mentor' => 'Наставник',
    'foster-supporter' => 'Помощник передержек',
    'rescue-volunteer' => 'Волонтёр спасения',
    'lost-animal-search-supporter' => 'Помощник поиска животных',
    'successful-reunion-contributor' => 'Участник успешного возвращения',
    'adoption-supporter' => 'Помощник пристройства',
    'senior-animal-supporter' => 'Помощник пожилых животных',
    'special-needs-supporter' => 'Помощник животных с особыми потребностями',
    'local-guide' => 'Местный гид',
    'event-organizer' => 'Организатор мероприятий',
    'accessibility-contributor' => 'Автор по доступности',
    'community-reviewer' => 'Рецензент сообщества',
    'category-steward' => 'Куратор категории',
    'marketplace-reliability' => 'Надёжность на площадке объявлений',
];

return [
    'dimensions' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "Проверенный вклад «{$name}», учитываемый отдельно от других областей опыта.",
        ],
        $dimensions,
    ),
    'trust_levels' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "Уровень «{$name}» проверяется отдельно от кармы и профессиональных документов.",
        ],
        $trustLevels,
    ),
    'badges' => array_map(
        static fn (string $name): array => [
            'name' => $name,
            'description' => "Знак «{$name}» выдаётся по проверяемым критериям и может быть отозван после подтверждённого злоупотребления.",
        ],
        $badges,
    ),
    'events' => [
        'helpful_vote' => 'Другой участник отметил ответ как полезный.',
        'answer_accepted' => 'Автор темы принял ответ.',
        'reversal' => 'Предыдущее событие репутации отменено с сохранением аудита.',
    ],
    'messages' => [
        'self_award_forbidden' => 'Нельзя начислять репутацию самому себе.',
        'self_vote_forbidden' => 'Нельзя оценивать собственный ответ.',
        'self_accept_forbidden' => 'Нельзя принять собственный ответ.',
        'relationship_limit_reached' => 'Для этой пары участников достигнут текущий лимит влияния на репутацию.',
        'invalid_confirmation_risk' => 'Указанный класс риска подтверждения не поддерживается.',
        'invalid_confirmation_quorum' => 'Кворум подтверждения должен составлять от 2 до 50 рецензентов.',
        'invalid_confirmation_diversity' => 'Требование к разнообразию рецензентов должно соответствовать выбранному кворуму.',
        'invalid_confirmation_stance' => 'Выберите поддержку, возражение или воздержание.',
        'confirmation_conflict_required' => 'Опишите конфликт интересов перед отправкой оценки.',
        'confirmation_closed' => 'Это подтверждение закрыто или истекло.',
        'duplicate_confirmation_vote' => 'Вы уже оценили это подтверждение.',
    ],
];
