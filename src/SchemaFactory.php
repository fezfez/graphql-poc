<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use FezFez\GraphQLPoc\Security\GetUserFromContext;
use FezFez\GraphQLPoc\Security\IsAllowed;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Psr\Container\ContainerInterface;

class SchemaFactory
{
    public function __invoke(ContainerInterface $container, GetUserFromContext $getUserFromContext, IsAllowed $isAllowed): Schema
    {
        $parser            = ParserCache::getInstance();
        $typeLoader        = new TypeLoader($parser);
        $queryTypeResolver = new QueryTypeResolver($container, $parser, $getUserFromContext, $isAllowed);

        return new Schema(
            (new SchemaConfig())
                ->setQuery(new QueryType($parser, $typeLoader, $queryTypeResolver))
                ->setTypeLoader(static fn (string $name): Type => $typeLoader->byTypeName($name)),
        );
    }
}
