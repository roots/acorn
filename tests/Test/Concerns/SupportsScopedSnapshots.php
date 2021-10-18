<?php

namespace Roots\Acorn\Tests\Test\Concerns;

trait SupportsScopedSnapshots
{
    /* this does not work but maybe one day it will 🥺 */
    protected function getSnapshotDirectory(): string
    {
        $basepath = trim(preg_replace([
            '/' . class_basename($this) . '$/',
            '/^P\\\Tests/',
        ], '', get_class($this)), '\\/');

        return __DIR__ . DIRECTORY_SEPARATOR . $basepath . DIRECTORY_SEPARATOR . '__snapshots__';
    }
}
