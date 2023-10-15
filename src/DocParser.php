<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Throwable;

use function class_exists;
use function count;
use function file_get_contents;
use function sprintf;
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
    public function getReturnTypeFromDocBlock(ReflectionMethod $method, string $class): array
    {
        $return  = $method->getReturnType();
        $comment = $method->getDocComment();

        if ($return === null) {
            throw new Exception('return type is null');
        }

        if ($return->getName() === 'array') {
            $firstParamTag = $this->getReturnTags($comment);

            return ['isList' => true, 'of' => $this->parseGeneric($firstParamTag, $class), 'ici' => 'a'];
        }

        try {
            $firstParamTag = $this->getReturnTags($comment);
            if (class_exists($return->getName())) {
                return [
                    'isList' => false,
                    'isGenerrique' => true,
                    'of' => $return->getName(),
                    'child' => $this->parseGeneric($firstParamTag, $class),
                    'mustCreateType' => $return->getName() . '_TO_' . $this->parseGeneric($firstParamTag, $class),
                ];
            }

            return ['isList' => true, 'of' => $this->parseGeneric($firstParamTag, $class)];
        } catch (Throwable) {
        }

        return ['isList' => false, 'of' => $return->getName(), 'ici' => 'c'];
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

    private function parseGeneric(ReturnTagValueNode $firstParamTag, string|null $class): string
    {
        $type = $firstParamTag->type;

        if (! ($type instanceof GenericTypeNode)) {
            throw new Exception('not generic');
        }

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

        $tmp =  $genericTypes[0];

        return match ($tmp) {
            'int' => 'int',
            'bool' => 'bool',
            default => $this->resolveName($class, $tmp)
        };
    }

    private function resolveName(string $class, string $toResolve): string
    {
        $parser       = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $traverser    = new NodeTraverser();
        $nameResolver = new NameResolver();
        $rc           = new ReflectionClass($class);
// add your visitor
        $traverser->addVisitor(new NameResolver());

        $code = file_get_contents($rc->getFileName());

        // parse
        $stmts = $parser->parse($code);

        //echo $code;
        // traverse
        $stmts = $traverser->traverse($stmts);

        $found = (new NodeFinder())->findFirst($stmts, static function (Node $value) use ($toResolve) {
            return $value instanceof Name\FullyQualified && $value->getLast() === $toResolve;
        });

        if ($found === null || ! ($found instanceof Name\FullyQualified)) {
            throw new RuntimeException(sprintf('no resolve "%s"', $toResolve));
        }

        return substr($found->toCodeString(), 1);
    }
}
