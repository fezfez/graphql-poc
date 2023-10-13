<?php

declare(strict_types=1);

namespace FezFez\GraphQLPoc\Attribute;

use Attribute;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("outputType", type = "string"),
 *   @Attribute("prefetchMethod", type = "string"),
 *   @Attribute("for", type = "string[]"),
 *   @Attribute("description", type = "string"),
 *   @Attribute("inputType", type = "string"),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class GeneriqueMethod
{
}
