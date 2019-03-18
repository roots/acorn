<?php

namespace Roots\Acorn\Tests\Unit;

use Roots\Acorn\Tests\VirtualFileSystem;
use PHPUnit\Framework\TestCase;

class SageViewFinderTest extends TestCase
{
    use VirtualFileSystem;

    protected $fixtures = [
        '/app/plugins/my-plugin/resources/views/foo-bar.blade.php' => '{{-- foobar --}}',
        '/app/themes/sage/resources/views/page.blade.php' => '{{-- page --}}',
    ];

    /** @test */
    public function it_should_find_a_view_from_template_hierarchy()
    {
        $this->markTestIncomplete();
        $finder = $this->getFinder();

        $this->assertEquals([
            "{$this->filesystem}/app/themes/sage/resources/views/page.blade.php",
            "{$this->filesystem}/app/themes/sage/resources/views/page.php",
            "{$this->filesystem}/app/themes/sage/resources/views/page.css",
        ], $finder->find('page.php'));
    }

    /** @test */
    public function it_should_find_a_view_from_specified_plugin_folder()
    {
        $this->markTestIncomplete();
        $finder = $this->getFinder();
        $finder->getFinder()->addPath("{$this->filesystem}/app/plugins/my-plugin/resources/views");

        $this->assertEquals([
            "{$this->filesystem}/app/plugins/my-plugin/resources/views/foo-bar.blade.php",
            "{$this->filesystem}/app/plugins/my-plugin/resources/views/foo-bar.php",
            "{$this->filesystem}/app/plugins/my-plugin/resources/views/foo-bar.css",
            "{$this->filesystem}/app/plugins/my-plugin/resources/views/foo.blade.php",
            "{$this->filesystem}/app/plugins/my-plugin/resources/views/foo.php",
            "{$this->filesystem}/app/plugins/my-plugin/resources/views/foo.css",
        ], $finder->find('foo-bar.php'));
    }

    /** @test */
    public function it_should_find_a_given_page_template()
    {
        $this->markTestIncomplete();
        $finder = $this->getFinder();

        $this->assertEquals([
            "{$this->filesystem}/app/themes/sage/resources/views/page.blade.php",
            "{$this->filesystem}/app/themes/sage/resources/views/page.php",
            "{$this->filesystem}/app/themes/sage/resources/views/page.css",
        ], $finder->find('views/page.php'));
    }

    /** @test */
    public function it_should_find_arbitrary_view_file()
    {
        $this->markTestIncomplete();
        $finder = $this->getFinder();

        $this->assertEquals([
            "{$this->filesystem}/app/themes/sage/resources/../../../plugins/my-plugin/resources/views/foo-bar.blade.php",
            "{$this->filesystem}/app/themes/sage/resources/../../../plugins/my-plugin/resources/views/foo-bar.php",
            "{$this->filesystem}/app/themes/sage/resources/../../../plugins/my-plugin/resources/views/foo-bar.css",
            "{$this->filesystem}/app/themes/sage/resources/../../../plugins/my-plugin/resources/views/foo.blade.php",
            "{$this->filesystem}/app/themes/sage/resources/../../../plugins/my-plugin/resources/views/foo.php",
            "{$this->filesystem}/app/themes/sage/resources/../../../plugins/my-plugin/resources/views/foo.css",
        ], $finder->find('foo-bar.php'));
    }

    protected function getFinder()
    {
        return new \Roots\Sage\ViewFinder(
            new \Roots\Acorn\View\FileViewFinder(
                new \Illuminate\Filesystem\Filesystem(),
                ["{$this->filesystem}/sage/resources/views"]
            )
        );
    }
}
