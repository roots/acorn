<?php

namespace Roots\Acorn\Tests\Unit;

use PHPUnit\Framework\TestCase;

class AssetDirectiveTest extends TestCase
{
    /** @test */
    public function it_should_return_call_to_helper_function_when_invoked()
    {
        $directive = new \Roots\Acorn\Assets\AssetDirective();

        $this->assertEquals("<?= \Roots\asset('scripts/app.js'); ?>", $directive("'scripts/app.js'"));
        $this->assertEquals('<?= \Roots\asset($file); ?>', $directive('$file'));
    }
}
