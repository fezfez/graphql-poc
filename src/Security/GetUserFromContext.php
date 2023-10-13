<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Security;

interface GetUserFromContext
{
    public function get($context): UserFormContext;
}
