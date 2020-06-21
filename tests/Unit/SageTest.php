<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\Sage\Sage;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\Tests\Unit\TestDouble\ViewFactoryStub;
use Roots\Acorn\View\FileViewFinder;

class SageTest extends TestCase
{
    /** @var Sage */
    private $sage;
    /** @var FileViewFinder */
    private $fileViewFinder;
    /** @var ViewFactoryStub */
    private $viewFactory;

    /** @test */
    public function it_should_compile_list_of_theme_page_templates()
    {
        $this->fileViewFinder->setPaths([__DIR__ . '/__fixtures__/theme']);

        $result = $this->sage->filterThemeTemplates([], ['sage'], 123, 'page');

        self::assertSame(['page-template.php' => 'Page Template'], $result);
    }

    /** @test */
    public function it_should_compile_list_of_theme_post_type_templates()
    {
        $this->fileViewFinder->setPaths([__DIR__ . '/__fixtures__/theme']);

        $result = $this->sage->filterThemeTemplates([], ['sage'], 123, 'post');

        self::assertSame(['post-type-template.php' => 'Post Type Template'], $result);
    }

    /** @test */
    public function it_should_include_given_templates_in_compiled_list()
    {
        $this->fileViewFinder->setPaths([__DIR__ . '/__fixtures__/theme']);

        $result = $this->sage->filterThemeTemplates(['foo' => 'bar'], ['sage'], 123, 'page');

        self::assertSame(['foo' => 'bar', 'page-template.php' => 'Page Template'], $result);
    }

    /** @test */
    public function it_should_share_the_loop_post_with_view()
    {
        $post = new \WP_Post();

        $this->sage->filterThePost($post);

        self::assertSame(['post' => $post], $this->viewFactory->getShared());
    }

    /** @test */
    public function it_should_load_data_based_on_body_class()
    {
        $GLOBALS['body-class'] = ['foo', 'bar'];
        $this->fileViewFinder->setPaths([__DIR__ . '/__fixtures__/theme']);
        $log = [];
        $cb = function ($data, $view, $file) use (&$log): array {
            $log[] = [
                'data' => $data,
                'view' => $view,
                'file' => $file,
            ];
            return $data;
        };
        add_filter('sage/template/foo/data', $cb);
        add_filter('sage/template/bar/data', $cb);

        $this->sage->filterTemplateInclude(__DIR__ . '/__fixtures__/view.php');

        self::assertSame([], $log[0]['data']);
        self::assertSame(ltrim(__DIR__, '/') . '/__fixtures__/view', $log[0]['view']);
        self::assertSame(__DIR__ . '/__fixtures__/view.php', $log[0]['file']);
        self::assertSame([], $log[1]['data']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $filesystem = new Filesystem();
        $this->fileViewFinder = new FileViewFinder($filesystem, []);
        $this->viewFactory = new ViewFactoryStub();
        $this->sage = new Sage(
            $filesystem,
            new ViewFinder($this->fileViewFinder, $filesystem),
            $this->fileViewFinder,
            $this->viewFactory,
            new Application()
        );
    }
}
