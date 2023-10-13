<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;

use function array_key_exists;
use function lcfirst;
use function sprintf;
use function strtolower;

class TypeLoader
{
    /** @var array<string, Type&NamedType> */
    private static array $types = [];

    public static function byTypeName(string $shortName): mixed
    {
        return self::byTypeNameaaa(['isList' => false, 'of' => $shortName]);
    }

    /**
     * @return Type&NamedType
     *
     * @throws Exception
     */
    public static function byTypeNameaaa(array $description): Type
    {
        $shortName = array_key_exists('mustCreateType', $description) ? $description['mustCreateType'] : $description['of'];
        $cacheName = strtolower($shortName);

        if (! array_key_exists($cacheName, self::$types)) {
            self::$types[$cacheName] = self::resolveType($shortName);
        }

        if (array_key_exists('isList', $description) && $description['isList'] === true) {
            return Type::listOf(self::$types[$cacheName]);
        }

        return self::$types[$cacheName];
    }

    private static function resolveType(string $shortName): Type
    {
        $method = lcfirst($shortName);

        switch ($method) {
            case 'string':
                return self::string();

            case 'bool':
            case 'boolean':
                return self::boolean();

            case 'float':
                return self::float();

            case 'id':
                return self::id();

            case 'int':
                return self::int();
        }

        $generique = ParserCache::getInstance()->getGenerique();

        foreach (ParserCache::getInstance()->getTypes() as $type) {
            if ($type['class'] === $shortName) {
                $filds = [];

                foreach ($type['method'] as $exposedName => $method) {
                    $filds[$exposedName] = self::byTypeNameaaa($method['return']);
                }

                return new ObjectType([
                    'name' => $type['class'],
                    'fields' => $filds,
                    'resolveField' => static function (mixed $obj, array $args, $context, ResolveInfo $info) use ($type) {
                        if ($info->fieldName === 'items') {
                            return $obj->getItems($args);
                        }

                        $fieldName = $type['method'][$info->fieldName]['name'];

                        return $obj->$fieldName($args);
                    },
                ]);
            }
        }

        throw new Exception(sprintf('Unknown graphql type: %s', $shortName));
    }

    /** @throws InvariantViolation */
    public static function boolean(): ScalarType
    {
        return Type::boolean();
    }

    /** @throws InvariantViolation */
    public static function float(): ScalarType
    {
        return Type::float();
    }

    /** @throws InvariantViolation */
    public static function id(): ScalarType
    {
        return Type::id();
    }

    /** @throws InvariantViolation */
    public static function int(): ScalarType
    {
        return Type::int();
    }

    /** @throws InvariantViolation */
    public static function string(): ScalarType
    {
        return Type::string();
    }
}
