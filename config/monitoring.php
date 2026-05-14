<?php

return [
    'redis_health' => [
        'alert_to' => env('REDIS_HEALTH_ALERT_TO', env('MAIL_FROM_ADDRESS', '')),
        'alert_cc' => env('REDIS_HEALTH_ALERT_CC', ''),
        'reminder_minutes' => (int) env('REDIS_HEALTH_ALERT_REMINDER_MINUTES', 30),
    ],
    'ciclosvida_refresh' => [
        'report_to' => env('CICLOSVIDA_REFRESH_REPORT_TO', 'jsuarez@epsianaswayuu.com'),
    ],
];
