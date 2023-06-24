<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/** @template T */
#[Type]
class GenericCollection
{
    /** @var array<T> */
    private array $items;

    /** @param T ...$items */
    public function __construct(object ...$items)
    {
        $this->items = $items;
    }

    /** @return array<T> */
    #[Field]
    public function getItems(): array
    {
        return $this->items;
    }
}
