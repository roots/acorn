<?php

namespace Roots\Acorn\Tests\Unit\Clover;

use Illuminate\Contracts\View\Factory;
use PHPUnit\Framework\TestCase;
use Roots\Acorn\Application;
use Roots\Acorn\Clover\Meta;
use Roots\Acorn\Tests\Unit\TestDouble\CloverServiceProviderStub;
use Roots\Acorn\Tests\Unit\TestDouble\PluginStub;
use Roots\Acorn\Tests\Unit\TestDouble\ServiceProviderSpy;
use Roots\Acorn\Tests\Unit\TestDouble\ViewFactoryStub;

use function assert;

class ServiceProviderTest extends TestCase
{
    /** @var Application */
    private $app;
    /** @var PluginStub */
    private $plugin;
    /** @var CloverServiceProviderStub */
    private $service;
    /** @var ViewFactoryStub */
    private $viewFactory;

    public function testBootCallsRunAndLifecycle(): void
    {
        $this->service->boot();

        self::assertTrue($this->plugin->runWasCalled());
        self::assertTrue($this->plugin->lifecycleWasCalled());
    }

    public function testRegisterLoadsPluginConfigFileAndAddsViewPathsToFactory(): void
    {
        $this->service->register();

        self::assertContains('dummy-plugin', $this->getViewFactoryFromApp()->getRegisteredNamespaces());
        $paths = $this->getViewFactoryFromApp()->getPathsForNamespace('dummy-plugin');
        self::assertStringContainsString('tests/Unit/__fixtures__/plugin/config/../resources/views', $paths[0]);
    }

    public function testRegisterProvidersFromPluginConfig(): void
    {
        $this->service->register();

        self::assertTrue($this->getServiceProviderSpy()->isRegisterCalled());
    }

    public function testRegisterBailsEarlyIfNoConfigFound(): void
    {
        $meta = new Meta(
            [
                'key' => 'my-plugin',
                'plugin' => __DIR__ . '/../fixtures/not-existing/my-plugin.php',
            ]
        );
        $this->plugin = new PluginStub($meta);
        $this->app->bind('my-plugin', function () {
            return $this->plugin;
        });

        (new CloverServiceProviderStub($this->app, $meta))->register();

        $viewFactory = $this->getViewFactoryFromApp();
        self::assertNotContains('my-plugin', $viewFactory->getRegisteredNamespaces());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application();
        $meta = new Meta(
            [
                'key' => 'dummy-plugin',
                'plugin' => __DIR__ . '/../__fixtures__/plugin/dummy-plugin.php',
            ]
        );
        $this->plugin = new PluginStub($meta);
        $this->app->bind('dummy-plugin', function () {
            return $this->plugin;
        });
        $this->viewFactory = new ViewFactoryStub();
        $this->app->bind('view', function (): Factory {
            return $this->viewFactory;
        });
        $this->service = new CloverServiceProviderStub($this->app, $meta);
    }

    private function getViewFactoryFromApp(): Factory
    {
        $viewFactory = $this->app->get('view');
        assert($viewFactory instanceof ViewFactoryStub);
        return $viewFactory;
    }

    private function getServiceProviderSpy(): ServiceProviderSpy
    {
        $viewFactory = $this->app->get(ServiceProviderSpy::class);
        assert($viewFactory instanceof ServiceProviderSpy);
        return $viewFactory;
    }
}
