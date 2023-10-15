<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\assets;

use GraphQL\Error\DebugFlag;
use GraphQL\Server\Helper;
use GraphQL\Server\ServerConfig;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PsrHandler
{
    private Helper $helper;

    public function __construct(private ServerConfig $serverConfig)
    {
        $this->helper = new Helper();
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->exec($this->serverConfig->setDebugFlag(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS)->setContext($request), $request));
    }

    private function exec(ServerConfig $config, ServerRequestInterface $request)
    {
        return $this->helper->executeOperation($config, $this->helper->parsePsrRequest($request));
    }
}
