<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use function file_get_contents;
use function json_decode;

class ParserCache
{
    /** @return array<array{class: string, name: string, return: {class: string, name: string}}}> */
    private array $query;

    /** @return array<array{class: string, method: array<array{name: string, return: {class: string, name: string}}}> */
    private array $types;

    private static self|null $instance = null;

    private function __construct(array $data)
    {
        $this->query = $data['query'];
        $this->types = $data['type'];
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(json_decode(file_get_contents('graphql.json'), true));
        }

        return self::$instance;
    }

    /** @return array<array{class: string, name: string, return: {class: string, name: string}}}> */
    public function getQuery(): array
    {
        return $this->query;
    }

    /** @return array<array{class: string, method: array<array{name: string, return: {class: string, name: string}}}> */
    public function getTypes(): array
    {
        return $this->types;
    }
}
