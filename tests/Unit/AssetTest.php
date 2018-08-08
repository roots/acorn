<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{
    /**
     * Asset class does not access the file system
     * So these constants may refer to nonexistent paths
     */
    const FAKE_PATH = '/srv/www/example.com/current/web/app/themes/sage/dist';
    const FAKE_URI = '/app/themes/sage/dist';

    /** @test */
    public function it_should_return_the_original_relative_path()
    {
        $asset = $this->getAsset('scripts/main.js');

        $this->assertEquals('scripts/main.js', $asset->original());
    }

    /** @test */
    public function it_should_return_the_revved_relative_path()
    {
        $asset = $this->getAsset('scripts/main.js');

        $this->assertEquals('scripts/main-123456.js', $asset->revved());
    }

    /** @test */
    public function it_should_return_the_original_relative_path_if_revision_doesnt_exist()
    {
        $asset = $this->getAsset('scripts/notafile.js');

        $this->assertEquals('scripts/notafile.js', $asset->revved());
    }

    /** @test */
    public function it_should_prepend_manifest_uri()
    {
        $asset = $this->getAsset('scripts/main.js');
        $asset2 = $this->getAsset('scripts/notafile.js');

        $this->assertEquals(static::FAKE_URI . '/scripts/main-123456.js', $asset->uri());
        $this->assertEquals(static::FAKE_URI . '/scripts/notafile.js', $asset2->uri());
    }

    /** @test */
    public function it_should_prepend_manifest_path()
    {
        $asset = $this->getAsset('scripts/main.js');
        $asset2 = $this->getAsset('scripts/notafile.js');

        $this->assertEquals(static::FAKE_PATH . '/scripts/main-123456.js', $asset->path());
        $this->assertEquals(static::FAKE_PATH . '/scripts/notafile.js', $asset2->path());
    }

    /** @test */
    public function it_should_return_uri_when_converted_to_string()
    {
        $asset = $this->getAsset('scripts/main.js');

        $this->assertEquals($asset->uri(), (string) $asset);
    }

    /** @test */
    public function it_should_return_a_manifest()
    {
        $asset = $this->getAsset('scripts/main.js');

        $this->assertInstanceOf(\Roots\Acorn\Assets\Manifest::class, $asset->getManifest());
    }

    /**
     * @param string $path
     * @param array $manifest
     * @return \Roots\Acorn\Assets\Asset
     */
    protected function getAsset($path, $manifest = null)
    {
        $default = [
            "scripts/main.js" => "scripts/main-123456.js",
            "styles/main.css" => "styles/main-123456.css",
        ];

        $manifest = new \Roots\Acorn\Assets\Manifest($manifest ?? $default, static::FAKE_URI, static::FAKE_PATH);

        return new \Roots\Acorn\Assets\Asset($path, $manifest);
    }
}
