<?php

use Amp\Parallel\Sync\Channel;
use HaydenPierce\ClassFinder\ClassFinder;

return function (Channel $channel): \Generator {

    $args = \json_decode(yield $channel->receive(), true);
    if (!$args || !isset($args['namespace'])) {
        throw new \RuntimeException("Invalid arguments!");
    }

    $result = ClassFinder::getClassesInNamespace($args['namespace'], $args['options'] ?? ClassFinder::STANDARD_MODE);

    $result = \json_encode([
        'result' => $result,
    ]);

    if (!$result) {
        throw new \RuntimeException("Invalid result!");
    }

    yield $channel->send($result);

    return 0;

};
