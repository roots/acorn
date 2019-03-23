<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    /** @test */
    public function it_should_not_fire_boot_callbacks_more_than_once()
    {
        $app = new \Roots\Acorn\Application();

        $bootedCount = 0;
        $bootingCount = 0;
        $booted = function () use (&$bootedCount) {
            $bootedCount++;
        };
        $booting = function () use (&$bootingCount) {
            $bootingCount++;
        };

        $app->booted($booted);
        $app->booting($booting);

        /** initial boot sets counts to 1 */
        $app->boot();
        $this->assertEquals(1, $bootedCount);
        $this->assertEquals(1, $bootingCount);

        /** subsequent boot attempt skips firing callbacks */
        $app->boot();
        $this->assertEquals(1, $bootedCount);
        $this->assertEquals(1, $bootingCount);
    }
}
