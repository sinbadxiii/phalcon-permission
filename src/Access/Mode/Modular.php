<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access\Mode;

/**
 * Interface ModeInterface
 * @package Sinbadxiii\PhalconPermission\Access\Mode
 */
interface Modular
{
    /**
     * @return bool
     */
    public function notPrivate(): bool;

    /**
     * @return bool
     */
    public function isAllowedGuests(): bool;

    /**
     * @return string
     */
    public function resource(): string;

    /**
     * @param $resource
     * @return string
     */
    public function rewriteResource($resource): string;

    /**
     * @return string
     */
    public function action(): string;

    /**
     * @return string
     */
    public function controller(): string;

    /**
     * @param $controller
     * @return string
     */
    public function slugify($controller): string;
}