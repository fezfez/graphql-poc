<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use Exception;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionMethod;
use Throwable;

use function count;
use function substr;

class DocParser
{
    private Lexer $lexer;
    private PhpDocParser $phpDocParser;

    public function __construct()
    {
        $this->lexer        = new Lexer();
        $constExprParser    = new ConstExprParser();
        $typeParser         = new TypeParser($constExprParser);
        $this->phpDocParser = new PhpDocParser($typeParser, $constExprParser);
    }

    /** @return array{isList: bool, of: string} */
    public function getReturnTypeFromDocBlock(ReflectionMethod $method): array
    {
        $return  = $method->getReturnType();
        $comment = $method->getDocComment();

        if ($return === null) {
            throw new Exception('return type is null');
        }

        if ($return->getName() === 'array') {
            $firstParamTag = $this->getReturnTags($comment);

            return ['isList' => true, 'of' => $this->parseGeneric($firstParamTag)];
        }

        try {
            $firstParamTag = $this->getReturnTags($comment);
            if (class_exists($return->getName())) {
                return ['isGenerrique' => true, 'of' => $return->getName(), 'child' => $this->parseGeneric($firstParamTag)];
            }

            return ['isList' => true, 'of' => $this->parseGeneric($firstParamTag)];
        } catch (Throwable) {
        }

        return ['isList' => false, 'of' => $return->getName()];
    }

    private function getReturnTags(string|false $comment): ReturnTagValueNode
    {
        if ($comment === false) {
            throw new Exception('no comment');
        }

        $tokens     = new TokenIterator($this->lexer->tokenize($comment));
        $phpDocNode = $this->phpDocParser->parse($tokens); // PhpDocNode
        $returnTags = $phpDocNode->getReturnTagValues(); // ParamTagValueNode[]

        if (count($returnTags) !== 1) {
            throw new Exception('no param tag');
        }

        return $returnTags[0];
    }

    private function parseGeneric(ReturnTagValueNode $firstParamTag): string
    {
        $type = $firstParamTag->type;

        if (! ($type instanceof GenericTypeNode)) {
            throw new Exception('not generic');
        }
        dump($type);

        $genericTypes = [];
        foreach ($type->genericTypes as $index => $genericType) {
            $variance = $type->variances[$index] ?? GenericTypeNode::VARIANCE_INVARIANT;
            if ($variance !== GenericTypeNode::VARIANCE_INVARIANT) {
                throw new Exception('not invariant');
            }

            $genericTypes[] = (string) $genericType;
        }

        if (count($genericTypes) !== 1) {
            throw new Exception('not one generic');
        }

        if ($genericTypes[0][0] === '\\') {
            $genericTypes[0] = substr($genericTypes[0], 1);
        }

        return $genericTypes[0];
    }
}
