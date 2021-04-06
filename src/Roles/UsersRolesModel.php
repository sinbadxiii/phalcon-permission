<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Roles;

use Phalcon\Mvc\Model;
use Phalcon\Di;
use Sinbadxiii\PhalconPermission\Resources\ResourcesActionsModel;

class UsersRolesModel extends Model
{
    public function initialize()
    {
        $this->setSource(
            Di::getDefault()->getShared("config")->acl->table_user_roles
        );
    }
}