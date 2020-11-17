<?php

namespace Roots\Acorn\Support\Contracts;

interface Filter
{
    /**
     * Apply filter in system.
     *
     * @return void
     */
    public function apply(): void;

    /**
     * Get filter accepted arguments number.
     *
     * @return int
     */
    public function getAcceptedArgs(): int;

    /**
     * Get filter handle method.
     *
     * @return callable
     */
    public function getHandle(): callable;

    /**
     * Get filter priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Get filter tag.
     *
     * @return array
     */
    public function getTag(): iterable;

    /**
     * Remove filter from system.
     *
     * @return void
     */
    public function remove(): void;
}
