<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Security;

class UserFormContext
{
    public function __construct(public readonly object $user)
    {
    }
}
