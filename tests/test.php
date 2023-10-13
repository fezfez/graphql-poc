<?php

declare(strict_types=1);

use FezFez\GraphQLPoc\Fixtures\SampleQuery;
use FezFez\GraphQLPoc\SchemaFactory;
use FezFez\GraphQLPoc\Security\GetUserFromContext;
use FezFez\GraphQLPoc\Security\IsAllowed;
use FezFez\GraphQLPoc\Security\UserFormContext;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

chdir(__DIR__ . '/..');
include 'vendor/autoload.php';


$container                            = new Container();
$container[SampleQuery::class]        = new SampleQuery();
$container[GetUserFromContext::class] = new class implements GetUserFromContext {
    public function get(ServerRequestInterface $context): UserFormContext
    {
        $class     = new stdClass();
        $class->id = (int) $context->getAttribute('myUser');

        return new UserFormContext($class);
    }
};
$container[IsAllowed::class]          = new class implements IsAllowed {
    public function get(UserFormContext $userFormContext): bool
    {
        return $userFormContext->user->id === 10;
    }
};

$psr11 = new PsrContainer($container);


class HandlerMe
{
    public function __construct(private Schema $schema)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $input = json_decode($request->getBody()->__toString(), true);

        $query          = $input['query'] ?? '';
        $variableValues = $input['variables'] ?? null;
        //$debug          = DebugFlag::NONE | DebugFlag::RETHROW_INTERNAL_EXCEPTIONS | DebugFlag::RETHROW_UNSAFE_EXCEPTIONS;
        //$debug          = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;

        $serverConfig = GraphQL::executeQuery($this->schema, $query, null, $request, $variableValues);

        $result = $serverConfig->toArray();

        return new JsonResponse($result, count($serverConfig->errors) ? 500 : 200);
    }
}




$handle = new HandlerMe((new SchemaFactory())->__invoke($psr11));

$request = (new ServerRequest())
    ->withUri(new Uri('http://example.com'))
    ->withMethod('POST')
    ->withAddedHeader('Authorization', 'Bearer toyoyo')
    ->withAddedHeader('Content-Type', 'application/json')
    ->withBody((new StreamFactory())->createStream(json_encode([
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
    ])))
    ->withAttribute('myUser', '10');

dump($handle->__invoke($request)->getBody()->getContents());


$request = (new Laminas\Diactoros\ServerRequest())
    ->withUri(new Laminas\Diactoros\Uri('http://example.com'))
    ->withMethod('POST')
    ->withAddedHeader('Authorization', 'Bearer toyoyo')
    ->withAddedHeader('Content-Type', 'application/json')
    ->withBody((new StreamFactory())->createStream(json_encode([
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
    ])))
    ->withAttribute('myUser', '55');

dump($handle->__invoke($request)->getBody()->getContents());


echo "\n";
