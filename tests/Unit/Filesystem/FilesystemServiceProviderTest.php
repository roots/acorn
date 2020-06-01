<?php

namespace Roots\Acorn\Tests\Unit\Filesystem;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Application;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\Filesystem\FilesystemServiceProvider;

class FilesystemServiceProviderTest extends TestCase
{
    public function testSingletonForFilesystemIsRegistered(): void
    {
        $app = new Application();
        $fsSp = new FilesystemServiceProvider($app);

        $fsSp->register();

        $fs = $app->get('files');
        self::assertInstanceOf(Filesystem::class, $fs);
    }
}
