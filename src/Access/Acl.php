<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use Phalcon\Cache;
use Phalcon\Di;
use Sinbadxiii\PhalconPermission\Exceptions\NotAuthorizedException;
use Sinbadxiii\PhalconPermission\Exceptions\DoesntAnnotation;
use Sinbadxiii\PhalconPermission\Providers\ProviderInterface;
use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;
use Sinbadxiii\PhalconPermission\Access\Enum as AclEnum;
use Sinbadxiii\PhalconPermission\Roles\RolesModel;

use function strtolower;
use function implode;
use function unserialize;
use function serialize;
use function in_array;

class Acl
{
    protected const CACHE_KEY = "acl";

    protected array $privates = [];

    protected Memory $acl;
    protected Cache $cache;
    protected $config;

    protected ProviderInterface $provider;

    public function __construct($config)
    {
        $this->config  = $config;

        foreach ($this->getModules() as $module => $options) {
            if ($options['scope'] === AclEnum::SCOPE_PRIVATE && !isset($this->privates[$module])) {
                $this->privates[] = $module;
            }
        }

        $this->cache = Di::getDefault()->getShared('cache');
    }

    public function isAllowed($dispatcher)
    {
        $moduleName = $dispatcher->getModuleName();
        $controller = $dispatcher->getControllerClass();

        $this->buildMap();

        if (
            !$this->isPublished() ||
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

        if ($this->config->access->endpoint === 'type') {

            $data = Di::getDefault()->getShared('annotations')
                ->getMethod($controller, $actionSlug . 'Action');

            try {
                $typeAccess = $data->get('accessType')->getArguments()[0];
                $actionSlug = $typeAccess;
            }
            catch (\Throwable $t) {
                throw new DoesntAnnotation($t->getMessage());
//                $actionSlug = "default";
            }

        }

        $user = Di::getDefault()->getShared('auth')->user();

        if (!$user) {
            throw new NotAuthorizedException("User is not authorized");
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
            $this->config->access->default === AclEnum::ACCESS_DENY ? Enum::DENY : Enum::ALLOW
        );

        $roles     = RolesModel::find();
        $resources = ResourcesModel::find();

        foreach ($resources as $resource) {
            $actions = $resource->getActionsName();
            $aclManager->addComponent($resource->name, $actions);
        }

        foreach ($roles as $role) {
            $aclManager->addRole($role->name);
            foreach ($role->getPermissions() as $resource => $action) {
                $aclManager->allow($role->name, $resource, $action);
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

    public function destroy()
    {
        return $this->cache->delete($this->getKey());
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
            $this->config->ajax === AclEnum::ALWAYS;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->getAcl()->getRoles();
    }

    /**
     * @return mixed
     */
    public function getResources()
    {

        return $this->getAcl()->getComponents();
    }

    /**
     * @return mixed
     */
    private function getModules()
    {
        return $this->config->modules;
    }

    private function isPublished()
    {
        return $this->config->published === 'true';
    }


}