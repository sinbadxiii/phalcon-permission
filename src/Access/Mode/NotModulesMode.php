<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access\Mode;

use strtolower;

/**
 * Class NotModulesMode
 * @package Sinbadxiii\PhalconPermission\Access\Mode
 */
class NotModulesMode extends ModeAbstract implements Modular
{
    /**
     * @param $controller
     * @return string
     */
    public function slugify($controller): string
    {
        return strtolower($controller);
    }
}