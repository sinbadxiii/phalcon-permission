<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Contracts;

interface HavingPermission
{
    public function hasPermission($permission);
}