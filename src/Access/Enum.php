<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconPermission\Access;

class Enum
{
    public const SCOPE_PRIVATE = 'private';

    public const ALWAYS        = 'always';

    public const ACCESS_DENY   = 'deny';
    public const ACCESS_ALLOW  = 'allow';

    public const SUPER_GRANT   = "*";

    public const CONFIG_ENDPOINT_TYPE = 'type';
}