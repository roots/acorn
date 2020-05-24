<?php

namespace Roots\Acorn\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Application;
use Roots\Acorn\Exceptions\Handler;
use Roots\Acorn\Tests\Unit\TestDouble\OutputStub;
use RuntimeException;
use stdClass;

use function implode;

final class HandlerTest extends TestCase
{
    /** @var Handler */
    private $handler;
    /** @var Application */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Application();
        $this->handler = new Handler($this->container);
    }

    public function testRender(): void
    {
        $this->markTestIncomplete('What exactly is the $request param from render method');
        // If it is \Illuminate\Http\Request we are missing a dependency for it

        $this->handler->render(new stdClass(), new RuntimeException());
    }

    public function testRenderForConsoleRendersErrorMessage(): void
    {
        $output = new OutputStub();

        $this->handler->renderForConsole($output, new RuntimeException('Foo bar'));

        self::assertStringContainsString('Foo bar', implode(' ', $output->getWrittenMessages()));
    }
}
