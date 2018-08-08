<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Tests\VirtualFileSystem;

class ManifestTest extends TestCase
{
    use VirtualFileSystem {
        setUp as fsSetUp;
    }

    protected $fixtures = [
        '/app/themes/sage/dist/scripts/main-123456.js' => '/* javascript */',
        '/app/themes/sage/dist/styles/main-123456.css' => '/* css */',
    ];

    public function setUp()
    {
        $this->fsSetUp();
        $jsonManifest = json_encode($this->manifest());
        $this->write('/app/themes/sage/dist/assets.json', $jsonManifest);
    }

    /** @test */
    public function it_should_decode_json_as_manifest()
    {
        $manifest = \Roots\Acorn\Assets\Manifest::fromJson(get_theme_file_path('dist/assets.json'));

        $this->assertInstanceOf(\Roots\Acorn\Assets\Manifest::class, $manifest);
    }

    /** @test */
    public function it_should_accept_an_array_as_manifest()
    {
        $array = $this->manifest();
        $manifest = new \Roots\Acorn\Assets\Manifest($array);

        $this->assertSame($array, $manifest->toArray());
    }

    /** @test */
    public function it_should_return_path_and_uri_when_methods_are_called()
    {
        $uri = get_theme_file_uri('dist');
        $path = get_theme_file_path('dist');
        $manifest = new \Roots\Acorn\Assets\Manifest([], $uri, $path);

        $this->assertSame($uri, $manifest->uri());
        $this->assertSame($path, $manifest->path());
    }

    protected function manifest($manifest = [])
    {
        return $manifest + [
            "scripts/main.js" => "scripts/main-123456.js",
            "styles/main.css" => "styles/main-123456.css",
        ];
    }
}
