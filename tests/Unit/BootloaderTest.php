<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Tests\VirtualFileSystem;

class BootloaderTest extends TestCase
{
    use VirtualFileSystem;

    /** @test */
    public function it_should_not_be_ready_until_action_has_fired()
    {
        $bootloader = $this->getBootloader();

        $this->assertFalse($bootloader->ready());

        $this->boot();

        $this->assertTrue($bootloader->ready());
    }

    /** @test */
    public function it_should_defer_callbacks_until_ready()
    {
        $bootloader = $this->getBootloader();

        $expected = 'foo';

        $bootloader->call(function () use (&$expected) {
            $expected = 'foobar';
        });

        $this->assertEquals('foo', $expected);

        $this->boot();

        $this->assertEquals('foobar', $expected);
    }

    /** @test */
    public function it_should_immediately_call_callback_if_ready()
    {
        $bootloader = $this->getBootloader();

        $expected = 'foo';

        $this->boot();

        $bootloader->call(function () use (&$expected) {
            $expected = 'foobar';
        });

        $this->assertEquals('foobar', $expected);
    }

    protected function getBootloader($hook = 'boot')
    {
        return new \Roots\Acorn\Bootloader($hook, Application::class);
    }

    protected function boot($hook = 'boot')
    {
        \do_action($hook);
    }
}

class Application extends \Roots\Acorn\Application
{
    public function bootstrapWith(array $bootstrappers)
    {
    }
}
