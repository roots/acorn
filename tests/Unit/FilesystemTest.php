<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    /** @test */
    public function it_should_normalize_a_given_path()
    {
        $filesystem = new \Roots\Acorn\Filesystem\Filesystem();
        $path = '/some//path\\to/a\\\\file';

        $this->assertEquals('/some/path/to/a/file', $filesystem->normalizePath($path));
    }

    /** @test */
    public function it_should_determine_a_relative_path()
    {
        $filesystem = new \Roots\Acorn\Filesystem\Filesystem();

        $relative = $filesystem->getRelativePath(
            '/app/themes/sage/',
            '/app/plugins/my-plugin/'
        );

        $this->assertEquals('../../plugins/my-plugin/', $relative);
    }
}
