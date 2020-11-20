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
    public function calculateAcceptedArgs(): int;

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
     * Set filter priority.
     *
     * @param int $priority
     */
    public function setPriority(int $priority): void;

    /**
     * Set filter tag.
     *
     * @param iterable $tag
     */
    public function setTag(iterable $tag): void;

    /**
     * Remove filter from system.
     *
     * @return void
     */
    public function remove(): void;
}
