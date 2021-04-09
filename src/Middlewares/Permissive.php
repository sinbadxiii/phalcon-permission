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

        $this->redirectTo();
    }

    /**
     * @return false
     * @description custom redirect url, ex.
     *        return $this->dispatcher->forward(
     *            [
     *                'controller' => 'error',
     *                'action'     => 'error403'
     *            ]
     *       );
     */
    public function redirectTo()
    {
        return false;
    }
}