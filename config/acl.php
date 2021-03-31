<?php

declare(strict_types=1);

use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesActionsModel;

return [
    'acl' => [
        // models | file
        'provider' => 'models',

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
    ],
];
