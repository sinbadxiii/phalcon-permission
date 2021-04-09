<?php

declare(strict_types=1);

use Sinbadxiii\PhalconPermission\Permissions\PermissionsModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesActionsModel;

return [
    'published' => true,
    // models | file
    'provider' => 'models',

    'modules' => require_once ("modules.php"),

    'table_user_roles' => 'users_roles',

    'providers' => [
        'file' => [
            'src' => ""
        ],
        'models' => [
            'permissions' => PermissionsModel::class,
            'resources'   => ResourcesModel::class,
            'actions'     => ResourcesActionsModel::class
        ]
    ],


    'access' => [
        //action | type
        'endpoint' => 'type',

        //  access action by default
        // deny | allow
        'default' => 'deny',
    ],

    // access router by ajax  none | always
    'ajax' => "none"
];
