<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Roles;

use Phalcon\Di;
use Phalcon\Mvc\Model;
use Sinbadxiii\PhalconPermission\Permissions\PermissionsModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesActionsModel;
use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;
use Sinbadxiii\PhalconPermission\Contracts\HavingPermissions;

class RolesModel extends Model implements HavingPermissions
{
    public function initialize()
    {
        $this->setSource("roles");
    }

    public function permissions()
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