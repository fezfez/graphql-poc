<?php

declare(strict_types=1);

use FezFez\GraphQLPoc\Fixtures\SampleQuery;
use FezFez\GraphQLPoc\SchemaFactory;
use GraphQL\Error\DebugFlag;
use GraphQL\Server\OperationParams;
use GraphQL\Server\StandardServer;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

chdir(__DIR__ . '/..');
include 'vendor/autoload.php';


$container                     = new Container();
$container[SampleQuery::class] = new SampleQuery();
$psr11                         = new PsrContainer($container);

$server = new StandardServer([
    'schema' => (new SchemaFactory())->__invoke($psr11),
    'debugFlag' => DebugFlag::RETHROW_UNSAFE_EXCEPTIONS,
]);

$server->handleRequest(OperationParams::create([
    'query' => 'query {
        listOfMyDto {
            getToto
        },
        GenericCollectionOfMyDto {
            getItems {
                getToto
            }
        }
    }',
]));
