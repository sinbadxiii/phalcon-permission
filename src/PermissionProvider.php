<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class PermissionProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $providerName = 'permission';

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $di->setShared($this->providerName, function () {

        });
    }
}