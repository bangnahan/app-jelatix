<?php

return [
    'api_token' => env('MAILKETING_API_TOKEN', ''),
    'api_url' => env('MAILKETING_API_URL', 'https://api.mailketing.co.id/api/v1'),
    'from_email' => env('MAILKETING_FROM_EMAIL', 'noreply@jelatix.id'),
    'from_name' => env('MAILKETING_FROM_NAME', 'Jelatix Running Tickets'),
];
