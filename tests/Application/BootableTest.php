<?php

use Roots\Acorn\Application\Concerns\Bootable;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

it('does not boot WP-CLI when WP_CLI constant is not defined', function () {
    expect(class_exists('WP_CLI'))->toBeTrue();

    $app = Mockery::mock(BootableTestApp::class)->makePartial()->shouldAllowMockingProtectedMethods();

    $app->shouldReceive('isBooted')->andReturn(false);
    $app->shouldReceive('runningInConsole')->andReturn(true);
    $app->shouldReceive('enableHttpsInConsole');

    $app->shouldNotReceive('bootWpCli');

    $app->bootAcorn();
});

/**
 * Minimal concrete class using the Bootable trait for testing.
 */
class BootableTestApp
{
    use Bootable;

    public function isBooted(): bool
    {
        return false;
    }

    public function runningInConsole(): bool
    {
        return false;
    }
}
