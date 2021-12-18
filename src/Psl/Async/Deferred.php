<?php

declare(strict_types=1);

namespace Psl\Async;

use Exception as RootException;
use Psl;

/**
 * The following class was derived from code of Amphp.
 *
 * https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/lib/Deferred.php
 *
 * Code subject to the MIT license (https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/LICENSE).
 *
 * Copyright (c) 2015-2021 Amphp ( https://amphp.org )
 *
 * @template T
 */
final class Deferred
{
    /**
     * @var Internal\State<T>
     */
    private Internal\State $state;

    /**
     * @var Awaitable<T>
     */
    private Awaitable $awaitable;

    public function __construct()
    {
        $this->state = new Internal\State();
        $this->awaitable = new Awaitable($this->state);
    }

    /**
     * Completes the operation with a result value.
     *
     * @param T $result Result of the operation.
     *
     * @throws Psl\Exception\InvariantViolationException If the operation is no longer pending.
     */
    public function complete(mixed $result): void
    {
        $this->state->complete($result);
    }

    /**
     * Marks the operation as failed.
     *
     * @param RootException $exception Throwable to indicate the error.
     *
     * @throws Psl\Exception\InvariantViolationException If the operation is no longer pending.
     */
    public function error(RootException $exception): void
    {
        $this->state->error($exception);
    }

    /**
     * @return bool True if the operation has completed.
     */
    public function isComplete(): bool
    {
        return $this->state->isComplete();
    }

    /**
     * @return Awaitable<T> The awaitable associated with this Deferred.
     */
    public function getAwaitable(): Awaitable
    {
        return $this->awaitable;
    }
}
