<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Security;

interface IsAllowed
{
    public function get(UserFormContext $userFormContext, string $right): bool;
}
