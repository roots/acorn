<?php

namespace Roots\Acorn\Tests\Test;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Roots\Acorn\Tests\Test\Concerns\SupportsGlobalStubs;
use Roots\Acorn\Tests\Test\Concerns\SupportsScopedFixtures;
use Roots\Acorn\Tests\Test\Concerns\SupportsWordPressStubs;

class TestCase extends MockeryTestCase
{
    use SupportsGlobalStubs;
    use SupportsWordPressStubs;
    use SupportsScopedFixtures;

    protected function setUp(): void
    {
        $this->clearStubs();
        $this->wordPressStubs();
        parent::setUp();
    }
}
