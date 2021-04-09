<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access\Mode;

use Phalcon\Mvc\Dispatcher;
use Sinbadxiii\PhalconPermission\Access\Enum as AclEnum;

abstract class ModeAbstract
{
    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * @return bool
     */
    public function isAllowedGuests(): bool
    {
        $controller = $this->dispatcher->getControllerClass();

        return !(new $controller)->authAccess();
    }

    /**
     * @return string
     */
    public function resource(): string
    {
        $controllerName = $this->dispatcher->getControllerName();

        return $this->rewriteResource($controllerName);
    }

    /**
     * @param $resource
     * @return string
     */
    public function rewriteResource($resource): string
    {
        return $this->slugify($resource);
    }

    /**
     * @return string
     */
    public function action(): string
    {
        return $this->dispatcher->getActionName();
    }

    /**
     * @return string
     */
    public function controller(): string
    {
        return $this->dispatcher->getControllerClass();
    }
}