<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Permissions;

use Phalcon\Mvc\Model;

class PermissionsModel extends Model
{
    public function initialize()
    {
        $this->setSource("permissions");
    }
}