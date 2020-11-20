<?php

namespace Roots\Acorn\Tests\Unit\Support\Mocks;

use Roots\Acorn\Support\Filter;

/**
 * Static filter mock class.
 * Only for test purposes.
 */
final class StaticFilterMock extends Filter
{
    /**
     * Some static handle without arguments.
     *
     * @return string
     */
    public static function staticHandle(): string
    {
        return "cool string returned";
    }

    /**
     * Some static handle with arguments.
     *
     * @param array $arg1
     * @param array $arg2
     * @param array $arg3
     *
     * @return array
     */
    public static function staticHandleWith3Args(array $arg1, array $arg2, array $arg3): array
    {
        // mega merge here
        return array_merge(
            $arg1,
            $arg2,
            $arg3
        );
    }
}
