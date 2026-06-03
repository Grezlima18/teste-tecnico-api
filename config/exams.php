<?php

return [
    'private_key' => env('EXAM_API_PRIVATE_KEY'),
    'random_failure_percent' => (int) env('EXAM_API_RANDOM_FAILURE_PERCENT', 0),
    'fake_results' => [
        'TESTO' => 'Testosterona total: 560 ng/dL',
        'HEMO' => 'Hemograma completo: sem alteracoes relevantes',
        'T4L' => 'T4 livre: 1.20 ng/dL',
    ],
];
