<?php

declare(strict_types=1);

use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesActionsModel;

return [

    // models | file
    'provider' => 'models',

    'modules' => require_once ("modules.php"),

    'providers' => [
        'file' => [
            'src' => ""
        ],
        'models' => [
            'resources' => ResourcesModel::class,
            'actions'   => ResourcesActionsModel::class
        ]
    ],

    //  access action by default
    'access' => [
        /**
         * deny | allow
         */
        'default' => 'deny',
    ],

    // access router by ajax  none | always
    'ajax' => "none"
];
