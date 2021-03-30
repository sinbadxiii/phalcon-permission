<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Middlewares;

use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

class Permissive extends Injectable
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        if ($this->acl->isAllowed($dispatcher)) {
            return true;
        }

        return $this->redirectTo();

        return false;
    }

    public function redirectTo()
    {
        //custom redirect url
    }
}