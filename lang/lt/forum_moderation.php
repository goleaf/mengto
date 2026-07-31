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
        'truthfulness' => 'Patvirtinu, kad, mano žiniomis, šis pranešimas yra teisingas.',
        'immediate_safety' => 'Tai gali kelti tiesioginę grėsmę saugumui.',
        'block_user' => 'Pateikus pranešimą užblokuoti šį naudotoją.',
    ],
    'messages' => [
        'report_submitted' => 'Jūsų pranešimas gautas.',
        'case_opened' => 'Atidaryta moderavimo byla.',
        'case_assigned' => 'Moderavimo byla priskirta peržiūrai.',
        'action_applied' => 'Pritaikytas moderavimo veiksmas.',
        'moderator_recused' => 'Moderatorius nusišalino nuo bylos.',
        'appeal_decided' => 'Apeliacijos peržiūra baigta.',
    ],
    'validation' => [
        'truthfulness_required' => 'Prieš pateikdami patvirtinkite, kad pranešimas yra teisingas.',
        'unsupported_subject' => 'Šio objekto negalima pranešti naudojant šią formą.',
        'immediate_safety_not_available' => 'Šiai pranešimo priežasčiai skubus saugumo eskalavimas negalimas.',
        'rate_limited' => 'Pateikta per daug pranešimų. Prieš bandydami dar kartą palaukite.',
        'end_required' => 'Laikinam apribojimui būtina pabaigos data.',
        'independent_review_required' => 'Šį veiksmą turi patvirtinti kitas įgaliotas vertintojas.',
        'appeal_reason_length' => 'Apeliaciją paaiškinkite bent 20 simbolių.',
        'invalid_appeal_outcome' => 'Pasirinkite palaikomą apeliacijos rezultatą.',
        'closed_case_assignment' => 'Užbaigtos moderavimo bylos priskirti negalima.',
        'case_report_limit' => 'Šioje byloje per daug susietų pranešimų interaktyviam veiksmui. Naudokite ribotų paketų veiklos procesą.',
        'appeal_already_decided' => 'Dėl šios apeliacijos sprendimas jau priimtas.',
        'invalid_recusal_reason' => 'Pasirinkite palaikomą nusišalinimo priežastį.',
    ],
];
