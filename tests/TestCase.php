<?php

namespace Roots\Acorn\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;

use function Brain\Monkey\tearDown;

class TestCase extends MockeryTestCase
{
    protected function fixture($path)
    {
        $basepath = trim(preg_replace([
            '/' . class_basename($this) . '$/',
            '/^P\\\Tests/',
        ], '', get_class($this)), '\\/');

        return __DIR__ . DIRECTORY_SEPARATOR . $basepath . DIRECTORY_SEPARATOR . '__fixtures__' . DIRECTORY_SEPARATOR . $path;
    }

    /* this does not work but maybe one day it will 🥺 */
    protected function getSnapshotDirectory(): string
    {
        $basepath = trim(preg_replace([
            '/' . class_basename($this) . '$/',
            '/^P\\\Tests/',
        ], '', get_class($this)), '\\/');

        return __DIR__ . DIRECTORY_SEPARATOR . $basepath . DIRECTORY_SEPARATOR . '__snapshots__';
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }
}
