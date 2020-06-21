<?php

namespace Roots\Acorn\Tests\Unit\Assets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Roots\Acorn\Application;
use Roots\Acorn\Assets\AssetsManager;
use Roots\Acorn\Assets\Contracts\Manifest;
use Roots\Acorn\Assets\RelativePathManifest;
use Roots\Acorn\Tests\Unit\TestDouble\ContainerStub;
use Roots\Acorn\Tests\Unit\TestDouble\ManifestSpy;

class AssetsManagerTest extends TestCase
{
    /** @var AssetsManager */
    private $manager;

    public function testRegister(): void
    {
        $expected = new RelativePathManifest('', '');

        $this->manager->register('foo', $expected);

        self::assertSame($expected, $this->manager->manifest('foo'));
    }

    public function testCustomCreatorIsCalledWhenStrategyIsRegistered(): void
    {
        $expected = new RelativePathManifest('', '');
        $this->manager->extend('foo', function () use ($expected): Manifest {
            return $expected;
        });

        self::assertSame($expected, $this->manager->manifest('bar', ['strategy' => 'foo']));
    }

    public function testConfig(): void
    {
        $expected = new RelativePathManifest('', '');
        $manager = new AssetsManager(
            new ContainerStub(['config' => ['assets.manifests.foo' => ['strategy' => 'bar']]])
        );
        $manager->extend('bar', function () use ($expected): Manifest {
            return $expected;
        });

        self::assertSame($expected, $manager->manifest('foo', null));
    }

    public function testGetDefaultManifest(): void
    {
        $manager = new AssetsManager(new ContainerStub(['config' => ['assets.default' => 'foo']]));

        self::assertSame('foo', $manager->getDefaultManifest());
    }

    public function testMissingStrategyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->manager->manifest('foo', ['strategy' => 'bar']);
    }

    public function testManifestGetsCalled(): void
    {
        $spy = new ManifestSpy();
        $manager = new AssetsManager(new ContainerStub(['config' => ['assets.default' => 'default']]));
        $manager->register('default', $spy);

        $manager->foobar();

        $calls = $spy->getCallLog();
        self::assertArrayHasKey('foobar', $calls);
        self::assertSame(1, $calls['foobar']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $app = new Application();
        $this->manager = new AssetsManager($app);
    }
}
