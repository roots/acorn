<?php

namespace Roots\Acorn\Tests\Unit\Exceptions;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Roots\Acorn\Application;
use Roots\Acorn\Exceptions\Handler;
use Roots\Acorn\Tests\Unit\TestDouble\OutputStub;
use RuntimeException;

use function implode;

final class HandlerTest extends TestCase
{
    /** @var Handler */
    private $handler;
    /** @var Application */
    private $container;
    /** @var string */
    private $env;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Application();
        $this->env = 'development';
        $this->container->bind('env', function () {
            return $this->env;
        });
        $this->handler = new Handler($this->container);
    }

    public function testRenderSymfonyErrorPageWithoutErrorMessageForProduction(): void
    {
        $this->env = 'production';

        $content = $this->handler->render(Request::create('/'), new RuntimeException('Foo Bar'));

        self::assertStringContainsString('500 Internal Server Error', $content);
        self::assertStringNotContainsString('Foo bar', $content);
    }

    public function testRenderForConsoleRendersErrorMessage(): void
    {
        $output = new OutputStub();

        $this->handler->renderForConsole($output, new RuntimeException('Foo bar'));

        self::assertStringContainsString('Foo bar', implode(' ', $output->getWrittenMessages()));
    }
}
