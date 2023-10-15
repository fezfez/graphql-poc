<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use FezFez\GraphQLPoc\Exception\NotAuthorized;
use FezFez\GraphQLPoc\Security\GetUserFromContext;
use FezFez\GraphQLPoc\Security\IsAllowed;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class QueryTypeResolver
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ParserCache $parser,
        private readonly GetUserFromContext $getUserFromContext,
        private readonly IsAllowed $isAllowed,
    ) {
    }

    public function __invoke($rootValue, array $args, ServerRequestInterface $context, string $class, string $method): mixed
    {
        $right      = $this->parser->getRightFor($class, $method);
        $parserArgs = $this->parser->getArgsFor($class, $method);
        $argsToPush = [];

        foreach ($parserArgs as $key => $arg) {
            if ($arg['injectUser']) {
                $argsToPush[] = $this->getUserFromContext->get($context);
                continue;
            }

            $argsToPush[] = $args[$arg['name']];
        }

        if ($right) {
            $user = $this->getUserFromContext->get($context);

            if (! $this->isAllowed->get($user, $right)) {
                throw new NotAuthorized(sprintf('not authorized to run %s', $method));
            }
        }

        return $this->container->get($class)->$method(...$argsToPush);
    }
}
