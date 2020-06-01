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
        '/app/themes/sage/resources/views/another-page.blade.php' => '{{-- page --}}',
        '/app/themes/sage-child/resources/views/another-page.blade.php' => '{{-- child-page --}}',
    ];
    /** @var \Roots\Acorn\View\FileViewFinder */
    private $viewFinder;
    /** @var \Roots\Acorn\Filesystem\Filesystem */
    private $fs;

    /** @test */
    public function it_should_compile_list_of_views_for_template_hierarchy()
    {
        $finder = $this->getFinder();

        $this->assertEquals(
            [
                "resources/views/page.blade.php",
                "resources/views/page.php",
                "resources/views/page.css",
                "resources/views/page.html",
                "../sage/resources/views/page.blade.php",
                "../sage/resources/views/page.php",
                "../sage/resources/views/page.css",
                "../sage/resources/views/page.html",
            ],
            array_values($finder->locate('page.php'))
        );
    }

    /** @test */
    public function it_should_support_multiple_files()
    {
        $finder = $this->getFinder();

        $templates = array_values($finder->locate(['page.php', 'another-page.php']));

        self::assertContains("resources/views/page.blade.php", $templates);
        self::assertContains("resources/views/another-page.blade.php", $templates);
    }

    /** @test */
    public function it_should_return_instance_of_view_finder()
    {
        $this->assertEquals($this->viewFinder, $this->getFinder()->getFinder());
    }

    /** @test */
    public function it_should_return_instance_of_filesystem()
    {
        $this->assertEquals($this->fs, $this->getFinder()->getFilesystem());
    }

    protected function getFinder()
    {
        return new \Roots\Acorn\Sage\ViewFinder($this->viewFinder, $this->fs, "{$this->filesystem}/sage-child");
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->fs = new \Roots\Acorn\Filesystem\Filesystem();
        $this->viewFinder = new \Roots\Acorn\View\FileViewFinder(
            new \Roots\Acorn\Filesystem\Filesystem(),
            [
                "{$this->filesystem}/sage-child/resources/views",
                "{$this->filesystem}/sage/resources/views"
            ]
        );
    }
}
