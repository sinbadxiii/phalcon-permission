<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Roles;

use Phalcon\Mvc\Model;
use Sinbadxiii\PhalconPermission\Resources\ResourcesActionsModel;

class UsersRolesModel extends Model
{
    public function initialize()
    {
        $this->setSource("admin_users_roles");
    }
}