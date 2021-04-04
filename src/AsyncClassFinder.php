<?php


namespace Nicodinus\PhpAsync\ClassFinder;


use Amp\Failure;
use Amp\Parallel\Context\Process;
use Amp\Promise;
use RuntimeException;
use function Amp\call;
use function assert;
use function json_decode;
use function json_encode;

class AsyncClassFinder
{
    const STANDARD_MODE = 1;
    const RECURSIVE_MODE = 2;

    //

    /**
     * @param string $namespace
     * @param int $options
     *
     * @return Promise<string[]>|Failure<RuntimeException>
     */
    public static function getClassesInNamespace(string $namespace, int $options): Promise
    {
        return call(function () use (&$namespace, &$options) {

            // Create a new child process that does some blocking stuff.
            $context = yield Process::run(__DIR__ . "/Internal/worker.php");

            assert($context instanceof Process);

            $args = [
                'namespace' => $namespace,
                'options' => $options,
            ];
            $args = json_encode($args);
            if (!$args) {
                throw new RuntimeException("Invalid arguments!");
            }

            yield $context->send($args);

            $result = yield $context->receive();
            if (!$result) {
                throw new RuntimeException("Invalid result!");
            }

            $result = json_decode($result, true);
            if (!$result || !isset($result['result'])) {
                throw new RuntimeException("Invalid result!");
            }

            yield $context->join();

            return $result['result'];

        });
    }
}