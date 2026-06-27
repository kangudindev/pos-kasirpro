<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission Configuration
    |--------------------------------------------------------------------------
    |
    | This is the configuration file for the Spatie Laravel Permission package.
    | It defines the models, cache, and other settings for permissions.
    |
    */

    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_roles' => 'model_has_roles',
        'model_has_permissions' => 'model_has_permissions',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',
        'model_morph_key' => 'model_id',
        'model_morph_type' => 'model_type',
    ],

    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'teams' => false,

    'cache' => [
        'store' => 'file',
        'key' => 'spatie.permission.cache',
        'prefix' => 'spatie.permission.cache',
        'ttl_in_seconds' => 60 * 60 * 24,
    ],

];
