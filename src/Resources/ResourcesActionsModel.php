<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Resources;

use Phalcon\Mvc\Model;

class ResourcesActionsModel extends Model
{
    public function initialize()
    {
        $this->setSource("resources_actions");
        $this->belongsTo('resource_id', ResourcesModel::class, 'id', ['alias' => 'resources']);
    }
}