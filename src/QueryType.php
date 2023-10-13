<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use GraphQL\Type\Definition\ObjectType;
use Psr\Container\ContainerInterface;

class QueryType extends ObjectType
{
    public function __construct(ParserCache $parser, ContainerInterface $container)
    {
        $definition = [
            'name' => 'Query',
            'fields' => [],
        ];

        foreach ($parser->getQuery() as $query) {
            $definition['fields'][$query['name']] = [
                'type' => TypeLoader::byTypeNameaaa($query['return']),
                'description' => 'Returns ' . $query['return']['of'],
                'resolve' => new QueryTypeResolver($container, $query, $parser),
            ];
        }

        parent::__construct($definition);
    }
}
