<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Resources;

use Phalcon\Mvc\Model;

class ResourcesModel extends Model
{
    public function initialize()
    {
        $this->setSource("resources");
    }
}