<?php

return [
    'enabled' => env('META_ENABLED', false),
    'pixel_id' => env('META_PIXEL_ID', '1426946475629785'),
    'access_token' => env('META_ACCESS_TOKEN'),
    'api_version' => env('META_API_VERSION', 'v26.0'),
    'test_event_code' => env('META_TEST_EVENT_CODE'),
    'site_url' => env('FRONTEND_URL', 'http://127.0.0.1:5173'),
    'catalog_ids_verified' => env('META_CATALOG_IDS_VERIFIED', false),
    'retention_days' => 30,
    'max_attempts' => 12,
];
