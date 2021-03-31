<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use Phalcon\Di;
use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;
use Sinbadxiii\PhalconPermission\Roles\RolesModel;

class Acl
{
    protected const CACHE_KEY     = "acl";
    protected const SCOPE_PRIVATE = 'private';
    protected const ALWAYS        = 'always';
    protected const ACCESS_DENY   = 'deny';
    protected const ACCESS_ALLOW  = 'allow';
    protected const SUPER_GRANT   = "*";

    protected $permission;
    protected $modules;
    protected $privates = [];

    protected $acl;
    protected $cache;
    protected $config;

    protected $provider;

    public function __construct($config, $modules = [])
    {
        $this->config  = $config;
        $this->modules = $modules;

        foreach ($this->modules as $module => $options) {
            if ($options['scope'] === self::SCOPE_PRIVATE && !isset($this->privates[$module])) {
                $this->privates[] = $module;
            }
        }

        $this->cache = Di::getDefault()->getShared('cache');

        $this->buildMap();
    }

    public function isAllowed($dispatcher)
    {
        $moduleName = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerClass();

        if (
            $this->isAllowedAjax() ||
            $this->notPrivate($moduleName) ||
            $this->isAllowedGuests($controller)
        ) {
            return true;
        }

        $componentSlug = strtolower(
            !empty($moduleName) ? implode('.', [
                $moduleName, $dispatcher->getControllerName()
            ]) : $dispatcher->getControllerName()
        );

        $actionSlug =  $dispatcher->getActionName();

        $user = Di::getDefault()->getShared('auth')->user();

        if (!$user) {
            die("here will be ExceptionNotAuth");
        }

        foreach ($user->roles as $role) {
            if ($this->acl->isAllowed($role->name, $componentSlug, $actionSlug)) {
                return true;
            }
        }
        return false;
    }

    private function buildMap(): void
    {
        $aclManager = $this->getAcl() ?: $this->rebuildingMap();
        $this->setAcl($aclManager);
    }

    public function rebuildingMap()
    {
        $aclManager = new Memory();

        $aclManager->setDefaultAction(
            $this->config->access->default === self::ACCESS_DENY ? Enum::DENY : Enum::ALLOW
        );

        $roles = RolesModel::find();

        foreach ($roles as $role) {
            $aclManager->addRole($role->name);
        }

        $resources = ResourcesModel::find();

        foreach ($resources as $resource) {
            $actions = $resource->getActionsName();
            $aclManager->addComponent($resource->name, $actions);
        }

        foreach ($roles as $role) {
            $aclManager->addRole($role->name);
            foreach ($resources as $resource) {
                $actions = $resource->getActionsName();
                $aclManager->allow($role->name, $resource->name, $actions);
            }
        }

        return $aclManager;
    }

    /**
     * @return mixed|null
     */
    private function getAcl()
    {
        return $this->cache->get($this->getKey()) ? unserialize($this->cache->get($this->getKey())) : null;
    }

    /**
     * @param Memory $aclManager
     */
    private function setAcl(Memory $aclManager)
    {
        $this->cache->set($this->getKey(), serialize($aclManager));
        $this->acl = $aclManager;
    }

    /**
     * @return string
     */
    private function getKey()
    {
        return self::CACHE_KEY;
    }

    /**
     * @param $controller
     * @return bool
     */
    private function isAllowedGuests($controller)
    {
        return !(new $controller)->authAccess();
    }

    /**
     * @param $moduleName
     * @return bool
     */
    private function notPrivate($moduleName)
    {
        return !in_array($moduleName, $this->privates);
    }

    private function isAllowedAjax()
    {
        /**
         * @todo подумать что делать с ajaxControllers
         */
        return Di::getDefault()->getShared('request')->isAjax() &&
            $this->config->ajax === self::ALWAYS;
    }

    public function getRoles()
    {
        return $this->getAcl()->getRoles();
    }

    public function getResources()
    {
        return $this->getAcl()->getComponents();
    }

}