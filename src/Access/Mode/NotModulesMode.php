<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access\Mode;

use Phalcon\Mvc\Dispatcher;
use Sinbadxiii\PhalconPermission\Access\Enum as AclEnum;
use strtolower;

/**
 * Class NotModulesMode
 * @package Sinbadxiii\PhalconPermission\Access\Mode
 */
class NotModulesMode extends ModeAbstract implements Modular
{
    /**
     * NotModulesMode constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return bool
     */
    public function notPrivate(): bool
    {
        return false;
    }

    /**
     * @param $controller
     * @return string
     */
    public function slugify($controller): string
    {
        return strtolower($controller);
    }
}
