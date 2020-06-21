<?php

namespace Roots\Acorn\Tests\Unit\Clover;

use PHPUnit\Framework\TestCase;
use Roots\Acorn\Clover\Meta;

class MetaTest extends TestCase
{
    public function testSetAfterInitializationTriggersError(): void
    {
        $meta = new Meta();

        $this->expectError();
        $this->expectErrorMessage('Meta is immutable and cannot be modified after instantiation.');

        $meta->set('foo');
    }

    public function testOverloadingGetReturnsMetaInstanceForAssocArrayValue(): void
    {
        $meta = new Meta(['foo' => ['bar' => 'Lorem Ipsum']]);

        self::assertInstanceOf(Meta::class, $meta->foo);
        self::assertSame('Lorem Ipsum', $meta->foo->bar);
    }

    public function testOverloadingGetReturnsValue(): void
    {
        $meta = new Meta(['foo' => ['bar']]);

        self::assertSame(['bar'], $meta->foo);
    }
}
