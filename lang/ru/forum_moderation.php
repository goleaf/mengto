<?php

declare(strict_types=1);

use App\Services\ForumModerationActionCatalog;
use App\Services\ForumReportReasonCatalog;

$labels = static fn (array $keys): array => array_combine(
    $keys,
    array_map(
        static fn (string $key): string => ucfirst(str_replace('-', ' ', $key)),
        $keys,
    ),
);

return [
    'reasons' => $labels(ForumReportReasonCatalog::KEYS),
    'actions' => $labels(ForumModerationActionCatalog::KEYS),
    'forms' => [
        'truthfulness' => 'Я подтверждаю, что, насколько мне известно, жалоба правдива.',
        'immediate_safety' => 'Ситуация может представлять непосредственную угрозу безопасности.',
        'block_user' => 'Заблокировать этого пользователя после отправки жалобы.',
    ],
    'messages' => [
        'report_submitted' => 'Ваша жалоба получена.',
        'case_opened' => 'Открыто дело модерации.',
        'case_assigned' => 'Дело модерации назначено на проверку.',
        'action_applied' => 'Применено действие модерации.',
        'moderator_recused' => 'Модератор заявил самоотвод по делу.',
        'appeal_decided' => 'Рассмотрение апелляции завершено.',
    ],
    'validation' => [
        'truthfulness_required' => 'Перед отправкой подтвердите правдивость жалобы.',
        'unsupported_subject' => 'На этот объект нельзя пожаловаться через данную форму.',
        'immediate_safety_not_available' => 'Для этой причины недоступна срочная эскалация безопасности.',
        'rate_limited' => 'Отправлено слишком много жалоб. Подождите перед повторной попыткой.',
        'end_required' => 'Для временного ограничения требуется дата окончания.',
        'independent_review_required' => 'Это действие требует одобрения другого уполномоченного рецензента.',
        'appeal_reason_length' => 'Опишите апелляцию не менее чем в 20 символах.',
        'invalid_appeal_outcome' => 'Выберите допустимый результат апелляции.',
        'closed_case_assignment' => 'Завершенное дело модерации нельзя назначить.',
        'case_report_limit' => 'В деле слишком много связанных жалоб для интерактивного действия. Используйте пакетный операционный процесс.',
        'appeal_already_decided' => 'По этой апелляции уже принято решение.',
        'invalid_recusal_reason' => 'Выберите допустимую причину самоотвода.',
    ],
];
