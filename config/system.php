<?php
return [
    // migrate
    'default_auth_id' => 1,
    'created_at_column' => ['field' => 'ins_datetime'],
    'updated_at_column' => ['field' => 'upd_datetime'],
    'deleted_at_column' => [],
    'del_flag_column' => ['field' => 'del_flag', 'active' => 0, 'deleted' => 1],
    'created_by_column' => ['field' => 'ins_id'],
    'updated_by_column' => ['field' => 'upd_id'],
    'deleted_by_column' => [],
    'status_column' => [],
    // guard
    'frontend_guard' => 'frontend',
    'backend_guard' => 'backend',
    // route
    'backend_domain' => env('BACKEND_DOMAIN', ''),
    'backend_alias' => env('BACKEND_ALIAS', 'admin'),
    'frontend_domain' => env('FRONTEND_DOMAIN', ''),
    'frontend_alias' => env('FRONTEND_ALIAS', '/'),
    'api_domain' => env('API_DOMAIN', ''),
    'api_alias' => env('API_ALIAS', 'api'),
    // config
    'static_version' => time(),
];
