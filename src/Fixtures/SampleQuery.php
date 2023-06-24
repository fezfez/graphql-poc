<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Query;

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

    /** @return list<\FezFez\GraphQLPoc\Fixtures\MyDto> */
    #[Query(name: 'listOfMyDto')]
    public function listOfMyDto(): array
    {
        return [new MyDto(), new MyDto()];
    }

    /** @return \FezFez\GraphQLPoc\Fixtures\GenericCollection<\FezFez\GraphQLPoc\Fixtures\MyDto> */
    #[Query(name: 'GenericCollectionOfMyDto')]
    public function GenericCollectionOfMyDto(): GenericCollection
    {
        return new GenericCollection(new MyDto(), new MyDto());
    }
}
