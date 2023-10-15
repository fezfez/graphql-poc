<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Attribute;

use Attribute;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Logged
{
}
