<?php

declare(strict_types=1);

use FezFez\GraphQLPoc\Fixtures\SampleQuery;
use FezFez\GraphQLPoc\SchemaFactory;
use FezFez\GraphQLPoc\Security\GetUserFromContext;
use FezFez\GraphQLPoc\Security\IsAllowed;
use FezFez\GraphQLPoc\Security\UserFormContext;
use GraphQL\Error\DebugFlag;
use GraphQL\Server\OperationParams;
use GraphQL\Server\StandardServer;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

chdir(__DIR__ . '/..');
include 'vendor/autoload.php';


$container                            = new Container();
$container[SampleQuery::class]        = new SampleQuery();
$container[GetUserFromContext::class] = new class implements GetUserFromContext {
    public function get($context): UserFormContext
    {
        return new UserFormContext(new stdClass());
    }
};
$container[IsAllowed::class]          = new class implements IsAllowed {
    public function get(UserFormContext $userFormContext): bool
    {
        return false;
    }
};

$psr11 = new PsrContainer($container);

$server = new StandardServer([
    'schema' => (new SchemaFactory())->__invoke($psr11),
    'debugFlag' => DebugFlag::RETHROW_UNSAFE_EXCEPTIONS,
]);

$server->handleRequest(OperationParams::create([
    'query' => 'query {
        listOfInt,
        returnBool,
        arrayOfInt,
        listOfMyDto {
            toto
        },
        GenericCollectionOfMyDto {
            items {
                toto
            }
        }
        
    }',
]));


echo "\n";
