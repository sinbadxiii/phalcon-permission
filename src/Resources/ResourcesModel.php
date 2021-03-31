<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Resources;

use Phalcon\Mvc\Model;

class ResourcesModel extends Model
{
    public function initialize()
    {
        $this->setSource("resources");

        $this->hasMany('id', ResourcesActionsModel::class, 'resource_id', ['alias' => 'actions']);
    }

    public function getActionsName()
    {
        return array_column($this->getActions()->toArray(), "name");
    }
}