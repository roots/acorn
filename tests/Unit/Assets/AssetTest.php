<?php

namespace Roots\Acorn\Tests\Unit\Assets;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Assets\Asset;

class AssetTest extends TestCase
{
    public function testContentsReturnsEmptyStringIfNotExists(): void
    {
        $asset = new Asset('foo', 'bar');

        self::assertSame('', $asset->contents());
    }

    public function testGetReturnsFalseIfNotExists(): void
    {
        $asset = new Asset('foo', 'bar');

        self::assertFalse($asset->get());
    }

    public function testIncludedFileContentIsReturned(): void
    {
        $temp = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($temp, "<?php\nreturn ['foo' => 'bar'];\n");
        $asset = new Asset($temp, 'bar');

        self::assertSame(['foo' => 'bar'], $asset->get());
    }

    public function testCastsToUri(): void
    {
        $asset = new Asset('foo', 'bar');

        self::assertSame('bar', (string)$asset);
    }

}
