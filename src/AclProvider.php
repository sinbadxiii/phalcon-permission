<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission;

use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Sinbadxiii\PhalconPermission\Access\Acl;

class AclProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $providerName = 'acl';

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $configAcl = Di::getDefault()->getShared("config")->acl;
        $modules   = Di::getDefault()->getShared("config")->modules;

        $di->setShared($this->providerName, function () use ($configAcl, $modules) {
            return new Acl($configAcl, $modules);
        });
    }
}