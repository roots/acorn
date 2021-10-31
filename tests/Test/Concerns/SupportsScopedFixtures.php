<?php

namespace Roots\Acorn\Tests\Test\Concerns;

trait SupportsScopedFixtures
{
    protected function fixture($path)
    {
        $basepath = trim(preg_replace([
            '/' . class_basename($this) . '$/',
            '/^P\\\Tests/',
        ], '', get_class($this)), '\\/');

        // ⚠ This relies on current file structure. ⚠
        // If this trait is refactored to a different path,
        // be sure to update the line below.
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $basepath . DIRECTORY_SEPARATOR . '__fixtures__' . DIRECTORY_SEPARATOR . $path;
    }
}
