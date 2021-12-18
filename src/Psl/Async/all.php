<?php

declare(strict_types=1);

namespace Psl\Async;

use Exception as RootException;
use Psl\Dict;

/**
 * Awaits all awaitables to complete concurrently.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Awaitable<Tv>> $awaitables
 *
 * @throws Exception\CompositeException If multiple awaitables failed at once.
 *
 * @return array<Tk, Tv> an array containing the results, preserving the original awaitables order.
 */
function all(iterable $awaitables): array
{
    $values = [];

    // Awaitable::iterate() to throw the first error based on completion order instead of argument order
    foreach (Awaitable::iterate($awaitables) as $index => $awaitable) {
        try {
            $values[$index] = $awaitable->await();
        } catch (RootException $exception) {
            $errors = [];
            foreach ($awaitables as $original) {
                if ($original === $awaitable) {
                    continue;
                }

                if (!$original->isComplete()) {
                    $original->ignore();
                } else {
                    try {
                        $original->await();
                    } catch (RootException $error) {
                        $errors[] = $error;
                    }
                }
            }

            if ($errors === []) {
                throw $exception;
            }

            throw new Exception\CompositeException([$exception, ...$errors], 'Multiple exceptions thrown while waiting.');
        }
    }

    return Dict\map_with_key(
        $awaitables,
        /**
         * @param Tk $key
         * @param Awaitable<Tv> $_awaitable
         *
         * @retun Tv
         */
        static fn(string|int $key, Awaitable $_awaitable): mixed => $values[$key],
    );
}
