<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use GraphQL\Type\Definition\ObjectType;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;

class QueryType extends ObjectType
{
    public function __construct(
        ParserCache $parser,
        TypeLoader $typeLoader,
        QueryTypeResolver $queryTypeResolver,
    ) {
        $fields = [];

        foreach ($parser->getQuery() as $query) {
            $args = [];

            foreach ($query['args'] as $arg) {
                if ($arg['hidden']) {
                    continue;
                }

                $args[$arg['name']] = [
                    'type' => $typeLoader->byTypeName($arg['type']),
                ];
            }

            $fields[$query['exposedName']] = [
                'type' => $typeLoader->byDescription(
                    $query['return']['of'],
                    $query['return']['isList'],
                    array_key_exists('mustCreateType', $query['return']) ? $query['return']['mustCreateType'] : null,
                ),
                'description' => 'Returns ' . $query['return']['of'],
                'resolve' =>  static fn ($rootValue, array $args, ServerRequestInterface $context) => $queryTypeResolver->__invoke($rootValue, $args, $context, $query['class'], $query['name']),
                'args' => $args,
            ];
        }

        parent::__construct([
            'name' => 'Query',
            'fields' => $fields,
        ]);
    }
}
