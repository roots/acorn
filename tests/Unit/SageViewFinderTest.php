<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Tests\VirtualFileSystem;

class SageViewFinderTest extends TestCase
{
    use VirtualFileSystem;

    protected $fixtures = [
        '/app/themes/sage/resources/views/page.blade.php' => '{{-- page --}}',
        '/app/themes/sage-child/resources/views/page.blade.php' => '{{-- child-page --}}',
    ];

    /** @test */
    public function it_should_compile_list_of_views_for_template_hierarchy()
    {
        $finder = $this->getFinder();

        $this->assertEquals([
            "resources/views/page.blade.php",
            "resources/views/page.php",
            "resources/views/page.css",
            "resources/views/page.html",
            "../sage/resources/views/page.blade.php",
            "../sage/resources/views/page.php",
            "../sage/resources/views/page.css",
            "../sage/resources/views/page.html",
        ], array_values($finder->locate('page.php')));
    }

    protected function getFinder()
    {
        return new \Roots\Acorn\Sage\ViewFinder(
            new \Roots\Acorn\View\FileViewFinder(
                new \Roots\Acorn\Filesystem\Filesystem(),
                [
                    "{$this->filesystem}/sage-child/resources/views",
                    "{$this->filesystem}/sage/resources/views"
                ]
            ),
            new \Roots\Acorn\Filesystem\Filesystem(),
            "{$this->filesystem}/sage-child"
        );
    }
}
