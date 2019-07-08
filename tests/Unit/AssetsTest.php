<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Tests\VirtualFileSystem;

class AssetsTest extends TestCase
{
    use VirtualFileSystem {
        setUp as fsSetUp;
    }

    protected $fixtures = [
        '/app/themes/sage/dist/scripts/app-123456.js' => '/* javascript */',
        '/app/themes/sage/dist/styles/app.css' => '/* css */',
    ];

    public function setUp() : void
    {
        $this->fsSetUp();
        $jsonManifest = json_encode([
            'scripts/app.js' => 'scripts/app-123456.js',
            'styles/app.css' => 'styles/app.css?id=123456',
        ]);
        $this->write('/app/themes/sage/dist/assets.json', $jsonManifest);
    }

    /** @test */
    public function an_asset_should_strip_query_string_from_its_path()
    {
        $asset = $this->asset('scripts/app.js?id=123456');

        $this->assertEquals(get_theme_file_path('/scripts/app.js'), $asset->path());
    }

    /** @test */
    public function an_asset_should_return_its_contents()
    {
        $path = 'dist/scripts/app.js';
        $contents = '/** my app */';
        file_put_contents(get_theme_file_path($path), $contents);

        $this->assertEquals($contents, $this->asset($path)->contents());
    }

    /** @test */
    public function an_asset_should_determine_whether_it_exists()
    {
        $asset = $this->asset('dist/scripts/app-123456.js');
        $asset2 = $this->asset('dist/scripts/notafile.js');

        $this->assertTrue($asset->exists());
        $this->assertFalse($asset2->exists());
    }

    /** @test */
    public function an_asset_should_return_its_path_and_uri()
    {
        $asset = $this->asset('scripts/app.js', 'scripts/app.js?id=123456');

        $this->assertEquals(get_theme_file_path('scripts/app.js'), $asset->path());
        $this->assertEquals(get_theme_file_uri('scripts/app.js?id=123456'), $asset->uri());
    }

    /** @test */
    public function a_relative_path_manifest_should_prepend_a_base_path_and_uri()
    {
        $path = get_theme_file_path('dist');
        $uri = get_theme_file_uri('dist');
        $manifest = new \Roots\Acorn\Assets\RelativePathManifest($path, $uri, [
            'scripts/app.js' => 'scripts/app-123456.js'
        ]);

        $this->assertEquals($manifest->uri(), $uri);
        $this->assertEquals($manifest->path(), $path);
        $this->assertEquals($manifest->get('scripts/app.js')->uri(), "{$uri}/scripts/app-123456.js");
        $this->assertEquals($manifest->get('scripts/app.js')->path(), "{$path}/scripts/app-123456.js");
    }

    /** @test */
    public function asset_manager_should_resolve_manifest()
    {
        $app = new \Roots\Acorn\Application();
        $app->singleton('files', \Roots\Acorn\Filesystem\Filesystem::class);

        $assets = new \Roots\Acorn\Assets\AssetsManager($app);

        $manifest = $assets->manifest('my-manifest', [
            'strategy' => 'relative',
            'manifest' => get_theme_file_path('dist/assets.json'),
            'path' => get_theme_file_path('dist'),
            'uri' => get_theme_file_uri('dist'),
        ]);

        $this->assertInstanceOf(\Roots\Acorn\Assets\RelativePathManifest::class, $manifest);
    }

    /**
     * @param string $path
     * @return \Roots\Acorn\Assets\Asset
     */
    protected function asset($path, $uri = null)
    {
        return new \Roots\Acorn\Assets\Asset(get_theme_file_path($path), get_theme_file_uri($uri ?? $path));
    }
}
