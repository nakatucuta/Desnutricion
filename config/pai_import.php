<?php

return [
    'format_version' => env('PAI_IMPORT_FORMAT_VERSION', 'pai-afiliados-v1'),
    'max_failed_retries' => (int) env('PAI_IMPORT_MAX_FAILED_RETRIES', 3),
];
