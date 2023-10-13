<?php


use FezFez\GraphQLPoc\Parser;

exec('composer dump');

require 'vendor/autoload.php';


$parser = new Parser();
file_put_contents('graphql.json', json_encode([
    'type' => $parser->getType(),
    'query' => $parser->getQuery(),
    'right' => $parser->getRight(),
    'generique' => $parser->getGenerique(),
], JSON_PRETTY_PRINT));

echo 'IR dump into ./graphql.json'."\n";

exit(1);