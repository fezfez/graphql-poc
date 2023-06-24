<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use olvlvl\ComposerAttributeCollector\Attributes;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Type;

class Parser
{
    private DocParser $docParser;

    public function __construct()
    {
        $this->docParser = new DocParser();
    }

    /** @return array<array{class: string, name: string, return: {class: string, name: string}}}> */
    public function getQuery(): array
    {
        $list = [];

        foreach (Attributes::findTargetMethods(Query::class) as $target) {
            $method = new ReflectionMethod($target->class, $target->name);
            $return = $this->docParser->getReturnTypeFromDocBlock($method);

            $list[] = ['class' => $target->class, 'name' => $target->name, 'return' => $return];
        }

        return $list;
    }

    /** @return array<array{class: string, method: array<array{name: string, return: {class: string, name: string}}}> */
    public function getType(): array
    {
        $list = [];

        foreach (Attributes::findTargetClasses(Type::class) as $target) {
            $fieldList = Attributes::filterTargetMethods(static function (string $attributName, string $className) use ($target) {
                return $className === $target->name && $attributName === Field::class;
            });

            $methodeList = [];

            foreach ($fieldList as $field) {
                $method = new ReflectionMethod($target->name, $field->name);
                $return = $this->docParser->getReturnTypeFromDocBlock($method);

                $methodeList[] = ['name' => $field->name, 'return' => $return];
            }

            $list[] = ['class' => $target->name, 'method' => $methodeList];
        }

        return $list;
    }
}
