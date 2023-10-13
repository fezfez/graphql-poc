<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Attribute;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("outputType", type = "string"),
 * })
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Query extends AbstractRequest
{
}
