<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access;

use Sinbadxiii\PhalconPermission\Access\Mode\Factory as FactoryMode;
use Sinbadxiii\PhalconPermission\Exceptions\NotAuthorizedException;
use Sinbadxiii\PhalconPermission\Exceptions\DoesntAnnotation;
use Sinbadxiii\PhalconPermission\Providers\ProviderInterface;
use Sinbadxiii\PhalconPermission\Resources\ResourcesModel;
use Sinbadxiii\PhalconPermission\Access\Enum as AclEnum;
use Sinbadxiii\PhalconPermission\Roles\RolesModel;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use Phalcon\Cache;
use Phalcon\Di;

use function strtolower;
use function implode;
use function unserialize;
use function serialize;
use function in_array;

/**
 * Class Acl
 * @package Sinbadxiii\PhalconPermission\Access
 */
class Acl
{
    protected const CACHE_KEY = "acl";

    /**
     * @var Memory
     */
    protected Memory $acl;

    /**
     * @var Cache|mixed
     */
    protected Cache $cache;

    /**
     * @var
     */
    protected $config;

    /**
     * @var ProviderInterface
     */
    protected ProviderInterface $provider;

    /**
     * Acl constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config  = $config;

        $this->cache = Di::getDefault()->getShared('cache');
    }

    /**
     * @param $dispatcher
     * @return bool
     * @throws DoesntAnnotation
     * @throws NotAuthorizedException
     */
    public function isAllowed($dispatcher)
    {
        $mode = (new FactoryMode($dispatcher))->get($this->getModules());

        $this->buildMap();

        if ($this->exceptionRuleAllowed($mode)) {
            return true;
        }

        $resource   = $mode->resource();
        $action     = $mode->action();
        $controller = $mode->controller();

        if ($this->config->access->endpoint === AclEnum::CONFIG_ENDPOINT_TYPE) {

            $annotations = Di::getDefault()->getShared('annotations');

            $dataAction   = $annotations->getMethod($controller, $action . 'Action');
            $dataResource = $annotations->get($controller)->getClassAnnotations();

            if (!$dataResource) {
                throw new DoesntAnnotation("No annotations found for " . $controller);
            }

            try {
                $accessResource = $dataResource->get('accessResource')->getArguments()[0];
                $resource       = $mode->rewriteResource($accessResource);
            }
            catch (\Throwable $t) {
                throw new DoesntAnnotation($controller . " " . $t->getMessage());
            }

            try {
                $action = $dataAction->get('accessType')->getArguments()[0];
            }
            catch (\Throwable $t) {
                throw new DoesntAnnotation($action . 'Action ' . $t->getMessage());
            }
        }

        $user = Di::getDefault()->getShared('auth')->user();

        if (!$user) {
            throw new NotAuthorizedException("User is not authorized");
        }

        foreach ($user->roles() as $role) {
            if ($this->acl->isAllowed($role->name, $resource, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     */
    private function buildMap(): void
    {
        $aclManager = $this->getAcl() ?: $this->rebuildingMap();
        $this->setAcl($aclManager);
    }

    /**
     * @return Memory
     */
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
            foreach ($role->permissions() as $resource => $action) {
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

    /**
     * @return bool
     * @throws Cache\Exception\InvalidArgumentException
     */
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
     * @return bool
     */
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
        return $this->config->modules->toArray() ?? [];
    }

    /**
     * @return bool
     */
    private function isActive()
    {
        return $this->config->published === 'true';
    }

    /**
     * @param $mode
     * @return bool
     */
    private function exceptionRuleAllowed($mode)
    {
        return (
            !$this->isActive() ||
            $this->isAllowedAjax() ||
            $mode->notPrivate() ||
            $mode->isAllowedGuests()
        );
    }
}