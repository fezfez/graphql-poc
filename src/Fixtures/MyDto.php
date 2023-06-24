<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class MyDto
{
    #[Field]
    public function getToto(): string
    {
        return 'toto';
    }
}
