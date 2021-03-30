<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Roles;

use Phalcon\Mvc\Model;

class RolesModel extends Model
{
    public function initialize()
    {
        $this->setSource("roles");
    }
}