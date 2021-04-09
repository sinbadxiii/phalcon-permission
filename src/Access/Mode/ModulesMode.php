<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access\Mode;

use Phalcon\Mvc\Dispatcher;
use Sinbadxiii\PhalconPermission\Access\Enum as AclEnum;

use function in_array;
use function strtolower;
use function sprintf;

/**
 * Class ModulesMode
 * @package Sinbadxiii\PhalconPermission\Access\Mode
 */
class ModulesMode extends ModeAbstract implements Modular
{
    /**
     * @var array
     */
    protected array $privates = [];

    /**
     * @var array
     */
    protected array $modules = [];

    /**
     * ModulesMode constructor.
     * @param Dispatcher $dispatcher
     * @param $modules
     */
    public function __construct(Dispatcher $dispatcher, $modules)
    {
        $this->dispatcher = $dispatcher;
        $this->modules    = $modules;

        foreach ($modules as $module => $options) {
            if ($options['scope'] === AclEnum::SCOPE_PRIVATE && !isset($this->privates[$module])) {
                $this->privates[] = $module;
            }
        }
    }

    /**
     * @return bool
     */
    public function notPrivate(): bool
    {
        $moduleName =  $this->dispatcher->getModuleName();

        return !in_array($moduleName, $this->privates);
    }

    /**
     * @param $controller
     * @return string
     */
    public function slugify($controller): string
    {
        $moduleName     = $this->dispatcher->getModuleName();
        return strtolower(sprintf("%s.%s", $moduleName, $controller));
    }
}