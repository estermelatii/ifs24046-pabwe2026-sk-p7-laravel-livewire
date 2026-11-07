<?php

return [
    // Mendukung nama variabel dari dosen (OPENAI_KEY, OPENAI_ENDPOINT) maupun nama standar (OPENAI_API_KEY, OPENAI_BASE_URL).
    'api_key' => env('OPENAI_KEY', env('OPENAI_API_KEY')),
    'base_url' => env('OPENAI_ENDPOINT', env('OPENAI_BASE_URL', 'https://api.openai.com/v1')),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'timeout' => (int) env('OPENAI_TIMEOUT', 60),
];
