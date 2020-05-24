<?php

namespace Roots\Acorn\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Exceptions\Handler\PrettyPageHandler;

final class PrettyPageHandlerTest extends TestCase
{
    /** @var PrettyPageHandler */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new PrettyPageHandler();
    }

    public function testPrettyPageHandlerHasExpectedDataTables(): void
    {
        $tables = $this->handler->getDataTables();

        self::assertArrayHasKey('WordPress Data', $tables);
        self::assertArrayHasKey('WP_Query Data', $tables);
        self::assertArrayHasKey('WP_Post Data', $tables);
    }
}
