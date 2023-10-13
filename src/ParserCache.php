<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use function file_get_contents;
use function json_decode;

class ParserCache
{
    /** @var array<array{class: string, name: string, return: {class: string, name: string}}}> */
    private array $query;

    /** @var array<array{class: string, method: array<array{name: string, return: {class: string, name: string}}}> */
    private array $types;
    /** @var array<array{class: string, name: string, right: string}}> */
    private array $right;
    /** @var array<array{class: string, name: string, right: string}}> */
    private array $generique;
    private static self|null $instance = null;

    private function __construct(array $data)
    {
        $this->query     = $data['query'];
        $this->types     = $data['type'];
        $this->right     = $data['right'];
        $this->generique = $data['generique'];
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

    /** @return array<array{class: string, name: string, right: string}}> */
    public function getRight(): array
    {
        return $this->right;
    }

    /** @return array<array{class: string, name: string, right: string}}> */
    public function getGenerique(): array
    {
        return $this->generique;
    }

    public function getRightFor(string $class, string $name): bool
    {
        foreach ($this->getRight() as $right) {
            if ($right['class'] === $class && $right['name'] === $name) {
                return true;
            }
        }

        return false;
    }
}
