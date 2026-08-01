<?php

declare(strict_types=1);

return [
    'fields' => [
        'allergy_knowledge_status' => 'What is known about allergies?',
        'medication_knowledge_status' => 'What is known about current medicines?',
    ],
    'knowledge_statuses' => [
        'known' => 'Known information is recorded',
        'none-known' => 'No known items in the available history',
        'not-provided' => 'Information was not provided',
        'unknown' => 'Unknown or still needs confirmation',
    ],
];
