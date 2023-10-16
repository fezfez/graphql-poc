<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use Exception;
use FezFez\GraphQLPoc\Attribute\Field;
use FezFez\GraphQLPoc\Attribute\GeneriqueMethod;
use FezFez\GraphQLPoc\Attribute\InjectUser;
use FezFez\GraphQLPoc\Attribute\Logged;
use FezFez\GraphQLPoc\Attribute\Query;
use FezFez\GraphQLPoc\Attribute\Right;
use FezFez\GraphQLPoc\Attribute\Type;
use olvlvl\ComposerAttributeCollector\Attributes;
use ReflectionMethod;
use ReflectionParameter;

use function array_key_exists;
use function assert;
use function count;
use function implode;
use function lcfirst;
use function sprintf;
use function str_replace;

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
        $list                = [];
        $exposedNameConflict = [];

        foreach (Attributes::findTargetMethods(Query::class) as $target) {
            $rClass = new \ReflectionClass($target->class);
            $method = new ReflectionMethod($target->class, $target->name);
            $return = $this->docParser->getReturnTypeFromDocBlock($method, $target->class);

            $args           = [];
            $mustInjectUser = static function (ReflectionParameter $parameter) {
                foreach ($parameter->getAttributes() as $attribute) {
                    if ($attribute->getName() === InjectUser::class) {
                        return true;
                    }
                }

                return false;
            };

            foreach ($method->getParameters() as $parameter) {
                $args[] = [
                    'name' => $parameter->getName(),
                    'type' => (string) $parameter->getType(),
                    'hidden' => $mustInjectUser($parameter),
                    'injectUser' => $mustInjectUser($parameter),
                ];
            }

            $attribute = $target->attribute;
            assert($attribute instanceof Query);
            $exposedName = $attribute->getName() ?? $target->name;

            if (! array_key_exists($exposedName, $exposedNameConflict)) {
                $exposedNameConflict[$exposedName] = [];
            }

            $exposedNameConflict[$exposedName][] = [
                'class' => $target->class,
                'name' => $target->name,
                'phpFile' => $rClass->getFileName(),
            ];

            $list[] = [
                'class' => $target->class,
                'name' => $target->name,
                'exposedName' => $exposedName,
                'logged' => $this->hasAttrForClassAndMethod($target->class, Logged::class, $target->name),
                'return' => $return,
                'args' => $args,
            ];
        }

        $message = null;
        foreach ($exposedNameConflict as $exposedName => $item) {
            if (count($item) === 1) {
                continue;
            }

            $conflictToString = [];
            foreach ($item as $value) {
                $conflictToString[] = sprintf('"%s->%s" (%s)',  $value['class'], $value['name'], $value['phpFile']);
            }

            $message .= '"' . $exposedName . '" found in ' . "\n- ".implode("\n- ", $conflictToString) . "\n";
        }

        if ($message !== null) {
            throw new Exception(sprintf('exposed name conflict' . "\n" . '%s', $message));
        }

        return $list;
    }

    /** @return array<array{class: string, name: string, right: string}}> */
    public function getRight(): array
    {
        $list = [];

        foreach (Attributes::findTargetMethods(Right::class) as $target) {
            $attribut = $target->attribute;

            assert($attribut instanceof Right);
            $list[$target->name] = ['class' => $target->class, 'right' => $attribut->getName()];
        }

        return $list;
    }

    /** @return array<array{class: string, name: string, right: string}}> */
    public function getGenerique(): array
    {
        $list = [];

        foreach (Attributes::findTargetMethods(GeneriqueMethod::class) as $target) {
            $attribut = $target->attribute;

            assert($attribut instanceof GeneriqueMethod);
            $list[] = ['class' => $target->class, 'name' => $target->name];
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
                $return = $this->docParser->getReturnTypeFromDocBlock($method, $target->name);

                $methodeList[lcfirst(str_replace('get', '', $field->name))] = [
                    'name' => $field->name,
                    'return' => $return,
                ];
            }

            $list[$target->name] = ['method' => $methodeList];
        }

        foreach ($this->getQuery() as $item) {
            if (! array_key_exists('mustCreateType', $item['return'])) {
                continue;
            }

            $methodeList = [];

            $fieldList = Attributes::filterTargetMethods(static function (string $attributName, string $className) use ($item) {
                return $className === $item['return']['of'] && $attributName === GeneriqueMethod::class;
            });

            foreach ($fieldList as $field) {
                $methodeList[lcfirst(str_replace('get', '', $field->name))] = [
                    'name' => $field->name,
                    'return' => ['of' => $item['return']['child'], 'isList' => true],
                ];
            }

            $list[$item['return']['mustCreateType']] = ['method' => $methodeList];
        }

        return $list;
    }

    private function hasAttrForClassAndMethod(string $class, string $attriut, string $method): bool
    {
        $list = Attributes::filterTargetMethods(static function (string $attributName, string $className) use ($class, $attriut) {
            return $className === $class && $attributName === $attriut;
        });

        foreach ($list as $item) {
            if ($item->name === $method) {
                return true;
            }
        }

        return false;
    }
}
