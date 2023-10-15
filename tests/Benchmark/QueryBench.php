<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Benchmark;

use FezFez\GraphQLPoc\assets\PsrHandler;
use FezFez\GraphQLPoc\Fixtures\SampleQuery;
use FezFez\GraphQLPoc\SchemaFactory;
use FezFez\GraphQLPoc\Security\GetUserFromContext;
use FezFez\GraphQLPoc\Security\IsAllowed;
use FezFez\GraphQLPoc\Security\UserFormContext;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Server\ServerConfig;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use Psr\Http\Message\ServerRequestInterface;

class QueryBench
{
    private PsrHandler $handle;

    public function __construct()
    {
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
            public function get(UserFormContext $userFormContext, string $right): bool
            {
                return $userFormContext->user->id === 10;
            }
        };

        $psr11 = new PsrContainer($container);

        $config = new ServerConfig();
        $config->setSchema((new SchemaFactory())->__invoke($psr11, $psr11->get(GetUserFromContext::class), $psr11->get(IsAllowed::class)));
        $config->setPromiseAdapter(new SyncPromiseAdapter());

        $this->handle = new PsrHandler($config);
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     */
    public function benchConsume(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('http://example.com'))
            ->withMethod('POST')
            ->withAddedHeader('Authorization', 'Bearer toyoyo')
            ->withAddedHeader('content-type', 'application/json')
            ->withAttribute('myUser', '10')
            ->withParsedBody([
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
            ]);

        $this->handle->__invoke($request)->getBody()->getContents();
    }
}
