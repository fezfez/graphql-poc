<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Fixtures;

use FezFez\GraphQLPoc\Attribute\Query;
use FezFez\GraphQLPoc\Attribute\Right;

class SampleQuery
{
    #[Query(name: 'returnBool')]
    public function returnBool(): bool
    {
        return true;
    }

    /** @return array<int> */
    #[Query(name: 'arrayOfInt')]
    public function arrayOfInt(): array
    {
        return [1, 2, 3];
    }

    /** @return list<int> */
    #[Query(name: 'listOfInt')]
    public function listOfInt(): array
    {
        return [1, 2, 3];
    }

    /** @return list<MyDto> */
    #[Query(name: 'listOfMyDto')]
    public function listOfMyDto(): array
    {
        return [new MyDto(), new MyDto()];
    }

    /** @return GenericCollection<MyDto> */
    #[Query(name: 'GenericCollectionOfMyDto')]
    #[Right(name :'users')]
    public function GenericCollectionOfMyDto(): GenericCollection
    {
        return new GenericCollection(new MyDto(), new MyDto());
    }
}
