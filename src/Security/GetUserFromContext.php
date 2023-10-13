<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Security;

use Psr\Http\Message\ServerRequestInterface;

interface GetUserFromContext
{
    public function get(ServerRequestInterface $context): UserFormContext;
}
