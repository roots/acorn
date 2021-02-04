<?php

namespace Roots\Acorn\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;

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
}
