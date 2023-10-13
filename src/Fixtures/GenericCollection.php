<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Fixtures;

use FezFez\GraphQLPoc\Attribute\GeneriqueMethod;

/** @template T */
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
    #[GeneriqueMethod]
    public function getItems(): array
    {
        return $this->items;
    }
}
