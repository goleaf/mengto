<?php

declare(strict_types=1);

return [
    'types' => [
        'community' => 'Общественная организация',
        'shelter' => 'Приют',
        'rescue' => 'Спасательная организация',
        'professional' => 'Профессиональная организация',
        'venue' => 'Площадка',
        'marketplace' => 'Организация маркетплейса',
        'platform' => 'Организация платформы',
    ],
    'statuses' => ['draft' => 'Черновик', 'active' => 'Активна', 'suspended' => 'Приостановлена', 'archived' => 'В архиве'],
    'verification_statuses' => [
        'not_assessed' => 'Не проверено', 'pending' => 'Ожидает проверки', 'verified' => 'Проверено',
        'expired' => 'Проверка истекла', 'rejected' => 'Проверка отклонена', 'disputed' => 'Проверка оспаривается',
    ],
    'roles' => ['owner' => 'Владелец', 'administrator' => 'Администратор', 'event_manager' => 'Менеджер событий', 'finance_manager' => 'Финансовый менеджер', 'safety_lead' => 'Руководитель по безопасности', 'marketplace_manager' => 'Менеджер маркетплейса', 'shelter_coordinator' => 'Координатор приюта', 'member' => 'Участник', 'auditor' => 'Аудитор с доступом только для чтения'],
    'membership_statuses' => ['invited' => 'Приглашен', 'active' => 'Активен', 'removed' => 'Удален', 'expired' => 'Истекло'],
    'invitation_statuses' => ['pending' => 'Ожидает ответа', 'accepted' => 'Принято', 'declined' => 'Отклонено', 'revoked' => 'Отозвано', 'expired' => 'Истекло'],
    'restriction_capabilities' => [
        'create_events' => 'Создавать события', 'publish_events' => 'Публиковать события', 'accept_registrations' => 'Принимать регистрации',
        'accept_payments' => 'Принимать платежи', 'access_participant_data' => 'Просматривать данные участников', 'run_check_in' => 'Проводить регистрацию на входе',
        'enter_results' => 'Вводить результаты', 'create_invitations' => 'Создавать приглашения',
    ],
    'pages' => [
        'index' => ['eyebrow' => 'Полномочия организации', 'title' => 'Организации', 'description' => 'Управляйте ролями, членством, приглашениями и ответственностью за события в едином пространстве.', 'create_eyebrow' => 'Новая организация', 'create_title' => 'Создать профиль организации', 'yours_title' => 'Ваши организации', 'yours_description' => 'Показаны только организации с действующим членством.'],
        'show' => ['eyebrow' => 'Пространство организации', 'title' => 'Пространство организации', 'description' => 'Членство и операционные полномочия организации.', 'identity_eyebrow' => 'Идентичность', 'identity_title' => 'Полномочия организации', 'members_eyebrow' => 'Членство', 'invite_title' => 'Пригласить участника', 'members_title' => 'Участники', 'safety_eyebrow' => 'Безопасность и доступ', 'restrictions_title' => 'Операционные ограничения'],
        'invitation' => ['eyebrow' => 'Приглашение для конкретной учетной записи', 'title' => 'Приглашение в организацию', 'description' => 'Проверьте организацию и роль перед ответом.', 'details_eyebrow' => 'Детали приглашения'],
    ],
    'fields' => ['name' => 'Название организации', 'summary' => 'Публичное описание', 'type' => 'Тип организации', 'public_region' => 'Публичный регион', 'verification' => 'Проверка', 'members' => 'Участники', 'owner' => 'Владелец', 'invite_email' => 'Эл. почта приглашенного', 'role' => 'Роль', 'expires_at' => 'Действует до', 'removal_reason' => 'Код причины удаления', 'capability' => 'Ограничиваемое действие', 'reason_code' => 'Код причины', 'suspension_reason' => 'Код причины приостановки'],
    'actions' => ['create' => 'Создать организацию', 'creating' => 'Создание...', 'open_workspace' => 'Открыть пространство', 'back_to_directory' => 'К списку организаций', 'invite' => 'Создать приглашение', 'remove_member' => 'Удалить участника', 'remove_confirmation' => 'Удалить участника и отозвать будущий доступ?', 'apply_restriction' => 'Применить ограничение', 'suspend' => 'Приостановить организацию', 'suspend_confirmation' => 'Приостановить организацию и ее операционные действия с событиями?', 'accept_invitation' => 'Принять приглашение', 'decline_invitation' => 'Отклонить приглашение'],
    'labels' => ['not_provided' => 'Не указано', 'invitation_link' => 'Одноразовая подписанная ссылка приглашения', 'reason' => 'Причина: :reason'],
    'empty' => ['title' => 'Организаций пока нет', 'description' => 'Создайте организацию или примите приглашение.', 'members' => 'Данные о членстве отсутствуют.', 'restrictions' => 'Активных операционных ограничений нет.'],
    'feedback' => ['created' => 'Организация создана.', 'invited' => 'Приглашение создано.', 'member_removed' => 'Доступ участника удален.', 'restricted' => 'Операционное ограничение применено.', 'suspended' => 'Организация приостановлена.', 'invitation_accepted' => 'Приглашение принято.', 'invitation_declined' => 'Приглашение отклонено.'],
    'validation' => ['summary' => 'Проверьте ошибки формы организации.', 'already_member' => 'У учетной записи уже есть активное членство.', 'invitation_pending' => 'Для учетной записи уже есть действующее приглашение.', 'invitation_unavailable' => 'Приглашение больше недоступно.', 'idempotency_conflict' => 'Ключ запроса уже относится к другой операции.'],
    'audit' => ['created' => 'Организация создана', 'member_invited' => 'Участник приглашен', 'invitation_accepted' => 'Приглашение принято', 'invitation_declined' => 'Приглашение отклонено', 'member_removed' => 'Участник удален', 'restriction_applied' => 'Ограничение применено', 'suspended' => 'Организация приостановлена', 'factory' => 'Тестовое событие аудита'],
];
