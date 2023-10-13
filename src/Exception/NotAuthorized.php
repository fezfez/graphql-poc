<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Exception;

use GraphQL\Error\ClientAware;
use RuntimeException;

class NotAuthorized extends RuntimeException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }
}
