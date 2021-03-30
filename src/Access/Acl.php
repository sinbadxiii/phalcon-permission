<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Component;
use Phalcon\Acl\Enum;
use Phalcon\Acl\Role;
use Phalcon\Di;
use Sinbadxiii\PhalconPermission\Roles\RolesModel;

class Acl
{
    protected const CACHE_KEY     = "acl";
    protected const SCOPE_PRIVATE = 'private';
    protected const ALWAYS        = 'always';

    protected $permission;
    protected $modules;
    protected $privates;

    protected $acl;
    protected $cache;
    protected $config;


    public function __construct($config, $modules)
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

        $componentSlug = strtolower(implode('.', [
            $moduleName,
            $dispatcher->getControllerName()
        ]));

        $actionSlug =  $dispatcher->getActionName();

        $user = Di::getDefault()->getShared('auth')->user();

        if (!$user) {
            die("exception not auth");
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
        $aclMemory = $this->getAcl();

        if (!$aclMemory) {
            $aclMemory = new Memory();
            $aclMemory->setDefaultAction(Enum::DENY);

            $roles = RolesModel::find();

            foreach ($roles as $role) {
                    $aclMemory->addRole($role->name);
            }

            $aclMemory->addComponent("crm.invoices", ["list", "edit"]);
            $aclMemory->addComponent("crm.storehouses", ["list", "edit"]);
            $aclMemory->addComponent("crm.contragents", ["list", "edit"]);
            $aclMemory->addComponent("crm.providers", ["list", "edit"]);

            $aclMemory->addComponent("admin.index", ["index"]);

            $aclMemory->allow("Администратор", "crm.invoices", "list");
            $aclMemory->allow("Администратор", "crm.storehouses", "*");
            $aclMemory->allow("Администратор", "crm.contragents", "*");
            $aclMemory->allow("Администратор", "crm.providers", "*");

            $aclMemory->allow("Администратор", "admin.index", "*");

            $this->setAcl($aclMemory);

        }
        $this->acl = $aclMemory;
    }

    /**
     * @return mixed|null
     */
    private function getAcl()
    {
        return $this->cache->get($this->getKey()) ? unserialize($this->cache->get($this->getKey())) : null;
    }

    /**
     * @param Memory $aclMemory
     */
    private function setAcl(Memory $aclMemory)
    {
        $this->cache->set($this->getKey(), serialize($aclMemory));
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
}