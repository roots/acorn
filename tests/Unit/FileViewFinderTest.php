<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Tests\VirtualFileSystem;

class FileViewFinderTest extends TestCase
{
    use VirtualFileSystem;

    protected $fixtures = [
        '/views/foo.blade.php' => '{{-- foo --}}',
        '/views/foo-bar.blade.php' => '{{-- foobar --}}',
        '/sage/resources/views/page.blade.php' => '{{-- page --}}',
        '/sage/resources/views/page-about.blade.php' => '{{-- about page --}}',
    ];

    /** @test */
    public function it_should_find_other_files_based_on_delimeter_breakpoints_in_specified_name()
    {
        $this->markTestSkipped("Feature has been removed, but might be added back");
        $finder = $this->getFinder();

        $this->assertEquals("{$this->filesystem}/views/foo.blade.php", $finder->find('foo'));
        $this->assertEquals("{$this->filesystem}/views/foo-bar.blade.php", $finder->find('foo-bar'));
        $this->assertEquals("{$this->filesystem}/views/foo-bar.blade.php", $finder->find('foo-bar-biz-baz'));
    }

    /** @test */
    public function it_should_find_possible_view_files_based_on_possible_file_name()
    {
        /** @var \Roots\Acorn\View\FileViewFinder $finder */
        $finder = $this->getFinder();

        $views = $finder->getPossibleViewFilesFromPath("{$this->filesystem}/sage/resources/views/page.php");

        $expected = [
            "page.blade.php",
            "page.php",
            "page.css",
            'page.html',
        ];

        $this->assertEquals($expected, $views);
    }

    protected function getFinder()
    {
        return new \Roots\Acorn\View\FileViewFinder(
            new \Illuminate\Filesystem\Filesystem(),
            ["{$this->filesystem}/views", "{$this->filesystem}/sage/resources/views"]
        );
    }
}
