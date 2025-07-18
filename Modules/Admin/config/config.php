<?php

return [
    'name' => 'Admin',
    'tabelList'=>[
        'users',
        'roles',
        'permissions',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
        'menu',
        'menu_has_permissions',
        'menu_has_roles',
        'menu_items',
        'password_resets',
        'personal_access_tokens',
        'rute_has_permission',
        'migrations',
        'data_rows',
        'data_types',
        'failed_jobs',
        'cache',
        'cache_locks',
        'job_batches',
        'jobs',
        'password_reset_tokens',
        'sessions'
        ] //list table yang di blacklist dari builder
];
