<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

use function array_key_exists;

class TypeLoader
{
    /** @var array<string, Type> */
    private array $types;
    /** @var array<string, ListOfType> */
    private array $list = [];

    public function __construct(private ParserCache $parser)
    {
        $this->types = [
            'string' => Type::string(),
            'bool' => Type::boolean(),
            'float' => Type::float(),
            'id' => Type::id(),
            'int' => Type::int(),
        ];
    }

    public function byTypeName(string $shortName): Type
    {
        return $this->byDescription($shortName, false, null);
    }

    public function byDescription(string $of, bool $isList, string|null $mustCreateType): Type
    {
        $shortName = $mustCreateType ?? ($of === 'boolean' ? 'bool' : $of);

        if (! array_key_exists($shortName, $this->types)) {
            $this->types[$shortName] = $this->buildType($shortName);
        }

        if (! $isList) {
            return $this->types[$shortName];
        }

        if (! array_key_exists($shortName, $this->list)) {
            $this->list[$shortName] =  Type::listOf($this->types[$shortName]);
        }

        return $this->list[$shortName];
    }

    private function buildType(string $shortName): ObjectType
    {
        $methods = $this->parser->getMethodsByTypeName($shortName);
        $filds   = [];

        foreach ($methods as $exposedName => $method) {
            $filds[$exposedName] = $this->byDescription(
                $method['return']['of'],
                $method['return']['isList'],
                array_key_exists('mustCreateType', $method['return']) ? $method['return']['mustCreateType'] : null,
            );
        }

        return new ObjectType([
            'name' => $shortName,
            'fields' => $filds,
            'resolveField' => static function (mixed $obj, array $args, $context, ResolveInfo $info) use ($methods) {
                $fieldName = $methods[$info->fieldName]['name'];

                return $obj->$fieldName($args);
            },
        ]);
    }
}
