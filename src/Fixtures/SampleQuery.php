<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Fixtures;

use FezFez\GraphQLPoc\Attribute\InjectUser;
use FezFez\GraphQLPoc\Attribute\Logged;
use FezFez\GraphQLPoc\Attribute\Query;
use FezFez\GraphQLPoc\Attribute\Right;
use FezFez\GraphQLPoc\Security\UserFormContext;

class SampleQuery
{
    #[Query(name: 'returnBool')]
    public function returnBool(): bool
    {
        return true;
    }

    /** @return array<int> */
    #[Query(name: 'arrayOfInt')]
    #[Logged]
    public function arrayOfInt(int $value): array
    {
        return [1, 2, 3, $value];
    }

    /** @return list<int> */
    #[Query(name: 'listOfInt')]
    public function listOfInt(
        #[InjectUser]
        UserFormContext $userFormContext,
    ): array {
        return [1, 2, 3, $userFormContext->user->id];
    }

    /** @return list<MyDto> */
    #[Query(name: 'listOfMyDto')]
    public function listOfMyDto(): array
    {
        return [new MyDto(), new MyDto()];
    }

    /** @return MyDto[] */
    #[Query(name: 'arrayOfMydro')]
    public function arrayOfMydro(): array
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

    /** @return GenericCollection[MyDto] */
    #[Query(name: 'myAlias')]
    #[Right(name :'users')]
    public function GenericCollectionOfMyDtoAsArray(): GenericCollection
    {
        return new GenericCollection(new MyDto(), new MyDto());
    }
}
