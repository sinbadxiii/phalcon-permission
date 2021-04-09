<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access\Mode;

use Phalcon\Mvc\Dispatcher;

/**
 * Class Factory
 * @package Sinbadxiii\PhalconPermission\Access\Mode
 */
class Factory
{
    /**
     * @var Dispatcher
     */
    protected Dispatcher$dispatcher;

    /**
     * Factory constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param $modules
     * @return ModulesMode|NotModulesMode
     */
    public function get($modules)
    {
        return (!empty($modules)) ?
            new ModulesMode($this->dispatcher, $modules) :
            new NotModulesMode($this->dispatcher);
    }
}