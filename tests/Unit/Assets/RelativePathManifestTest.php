<?php

namespace Roots\Acorn\Tests\Unit\Assets;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Assets\RelativePathManifest;

class RelativePathManifestTest extends TestCase
{
    private $path = '';
    private $uri = '';
    /** @var RelativePathManifest */
    private $manifest;

    public function testOffsetGet(): void
    {
        $asset = $this->manifest->offsetGet('foo');

        self::assertSame('/foo', $asset->path());
        self::assertSame('/foo', $asset->uri());
    }

    public function testOffsetSet(): void
    {
        $this->manifest->offsetSet('foo', 'bar');
        $asset = $this->manifest->offsetGet('foo');

        self::assertSame('/bar', $asset->path());
        self::assertSame('/bar', $asset->uri());
    }

    public function testOffsetUnset(): void
    {
        $this->manifest->offsetSet('foo', 'bar');
        $this->manifest->offsetUnset('foo');
        $asset = $this->manifest->offsetGet('foo');

        self::assertSame('/foo', $asset->path());
        self::assertSame('/foo', $asset->uri());
    }

    public function testOffsetExistsTrue(): void
    {
        $this->manifest->offsetSet('foo', 'bar');

        self::assertTrue($this->manifest->offsetExists('foo'));
    }

    public function testOffsetExistsFalse(): void
    {
        self::assertFalse($this->manifest->offsetExists('foo'));
    }

    public function testCountIsZeroWhenNothingAdded(): void
    {
        self::assertSame(0, $this->manifest->count());
    }

    public function testCountIsAsExpected(): void
    {
        $this->manifest->offsetSet('foo', 'bar');

        self::assertSame(1, $this->manifest->count());
    }

    public function testGetIterator(): void
    {
        $this->manifest->offsetSet('foo', 'bar');

        $iterator = $this->manifest->getIterator();
        self::assertTrue($iterator->offsetExists('foo'));
        self::assertSame('bar', $iterator->offsetGet('foo'));
    }

    public function testToArray(): void
    {
        $this->manifest->offsetSet('foo', 'bar');

        $array = $this->manifest->toArray();
        self::assertArrayHasKey('foo', $array);
        self::assertSame('bar', $array['foo']);
    }

    public function testToJson(): void
    {
        $this->manifest->offsetSet('foo', 'bar');

        $json = $this->manifest->toJson();
        $decoded = json_decode($json, true);
        self::assertArrayHasKey('foo', $decoded);
        self::assertSame('bar', $decoded['foo']);
    }

    public function testJsonSerializeReturnsSameDataAsToJson(): void
    {
        self::assertSame(json_encode($this->manifest, true), $this->manifest->toJson());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->manifest = new RelativePathManifest($this->path, $this->uri);
    }

}
