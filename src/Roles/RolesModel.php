<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Roles;

use Phalcon\Di;
use Phalcon\Mvc\Model;
use Sinbadxiii\PhalconPermission\Permissions\PermissionsModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesActionsModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;

class RolesModel extends Model
{
    public function initialize()
    {
        $this->setSource("roles");

        $this->hasMany('id', PermissionsModel::class, 'role_id', ['alias' => 'permissions']);
    }

    public function getPermissions()
    {
        $permissionsData = Di::getDefault()->getShared("modelsManager")
            ->createBuilder()
            ->from(['p' => PermissionsModel::class])
            ->columns(['r.name as resource', 'ra.name as action'])
            ->leftJoin(ResourcesModel::class,'r.id=p.resource_id', 'r')
            ->leftJoin(ResourcesActionsModel::class,'ra.id=p.action_id', 'ra')
            ->where("p.role_id=:role_id:", ['role_id' => $this->id])
            ->getQuery()
            ->execute();

        $permissions = [];
        foreach ($permissionsData as $permission) {
            $permissions[$permission->resource][] = $permission->action;
        }
        return $permissions;
    }
}