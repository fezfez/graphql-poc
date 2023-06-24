<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Psr\Container\ContainerInterface;

class SchemaFactory
{
    public function __invoke(ContainerInterface $container): Schema
    {
        $parser = ParserCache::getInstance();

        return new Schema(
            (new SchemaConfig())
                ->setQuery(new QueryType($parser, $container))
                ->setTypeLoader(static fn (string $name): mixed => TypeLoader::byTypeName($name)),
        );
    }
}
