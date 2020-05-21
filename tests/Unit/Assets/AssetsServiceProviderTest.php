<?php

namespace Roots\Acorn\Tests\Unit\Assets;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Application;
use Roots\Acorn\Assets\AssetsManager;
use Roots\Acorn\Assets\AssetsServiceProvider;
use Roots\Acorn\Assets\Contracts\Manifest;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\Tests\VirtualFileSystem;

class AssetsServiceProviderTest extends TestCase
{
    use VirtualFileSystem {
        setUp as fsSetUp;
    }

    protected $fixtures = [
        '/app/themes/sage/dist/scripts/app-123456.js' => '/* javascript */',
        '/app/themes/sage/dist/styles/app.css' => '/* css */',
    ];

    public function testRegister(): void
    {
        $app = new Application();
        $app->bind(
            'config',
            function (): array {
                return [
                    'assets.default' => 'test',
                    'assets.manifests.test' => ['manifest' => '', 'path' => 'test', 'uri' => 'test'],
                ];
            }
        );
        $app->bind(
            'files',
            function (): Filesystem {
                return new Filesystem();
            }
        );
        $service = new AssetsServiceProvider($app);

        $service->register();

        self::assertInstanceOf(AssetsManager::class, $app->get('assets'));
        self::assertInstanceOf(Manifest::class, $app->get('assets.manifest'));
    }

    public function setUp(): void
    {
        $this->fsSetUp();
        $jsonManifest = json_encode(
            [
                'scripts/app.js' => 'scripts/app-123456.js',
                'styles/app.css' => 'styles/app.css?id=123456',
            ]
        );
        $this->write('/app/themes/sage/dist/assets.json', $jsonManifest);
    }
}
