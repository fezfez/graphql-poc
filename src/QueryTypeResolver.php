<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use FezFez\GraphQLPoc\Exception\NotAuthorized;
use FezFez\GraphQLPoc\Security\GetUserFromContext;
use FezFez\GraphQLPoc\Security\IsAllowed;
use Psr\Container\ContainerInterface;

use function assert;

class QueryTypeResolver
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $query,
        private readonly ParserCache $parser,
    ) {
    }

    public function __invoke($rootValue, array $args, $context): mixed
    {
        $method = $this->query['name'];

        if ($this->parser->getRightFor($this->query['class'], $this->query['name'])) {
            $getUserFromContext = $this->container->get(GetUserFromContext::class);
            $isAllowed          = $this->container->get(IsAllowed::class);

            assert($getUserFromContext instanceof GetUserFromContext);
            assert($isAllowed instanceof IsAllowed);

            $user = $getUserFromContext->get($context);

            if (! $isAllowed->get($user)) {
                throw new NotAuthorized('not authorized');
            }
        }

        return $this->container->get($this->query['class'])->$method($args);
    }
}
