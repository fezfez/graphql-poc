<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Fixtures;

use FezFez\GraphQLPoc\Attribute\Field;
use FezFez\GraphQLPoc\Attribute\Type;

#[Type]
class MyDto
{
    #[Field]
    public function getToto(): string
    {
        return 'toto';
    }
}
