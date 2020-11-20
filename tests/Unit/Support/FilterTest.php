<?php

namespace Roots\Acorn\Tests\Unit\Support;

use PHPUnit\Framework\MockObject\MockBuilder;
use Roots\Acorn\Support\Filter;
use PHPUnit\Framework\TestCase;

require __DIR__ . '/Mocks/functions.php';

class FilterTest extends TestCase
{
    /**
     * Test setter and getter for priority.
     *
     * @return void
     */
    public function testSetPriorityValidValueSuccessfully(): void
    {
        $filter = $this->createFilterMockBuilder()->getMockForAbstractClass();
        $priority = mt_rand(10, 999);

        $filter->setPriority($priority);
        $this->assertEquals($priority, $filter->getPriority());
    }

    /**
     * Test set priority throws type error
     * on invalid value.
     *
     * @return void
     */
    public function testSetPriorityThrowsTypeErrorOnInvalidValue(): void
    {
        $this->expectException(\TypeError::class);

        $filter = $this->createFilterMockBuilder()->getMockForAbstractClass();
        $priority = 'hyper-priority';

        $filter->setPriority($priority);
    }

    /**
     * Test calculating accepted args count for handle.
     * Handle is object method without arguments.
     *
     * @return void
     */
    public function testCalculatingAcceptedArgsForObjectHandleMethodWithoutArguments(): void
    {
        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->addMethods(['handle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturnCallback([$filter, 'handle']);
        $filter->method('handle')->willReturn(function () {
            return 'without arguments';
        });

        $this->assertEquals(0, $filter->calculateAcceptedArgs());
    }

    /**
     * Test calculating accepted args count for handle.
     * Handle is object method with arguments.
     *
     * @return void
     */
    public function testCalculatingAcceptedArgsForObjectHandleMethodWithArguments(): void
    {
        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->addMethods(['handle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturnCallback([$filter, 'handle']);
        $filter->method('handle')->willReturn(function (int $a, int $b) {
            return $a + $b;
        });

        $this->assertEquals(2, $filter->calculateAcceptedArgs());
    }

    /**
     * Test calculating accepted args count for handle.
     * Handle is function without arguments.
     *
     * @return void
     */
    public function testCalculatingAcceptedArgsForHandleFunctionWithoutArguments(): void
    {
        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->addMethods(['handle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturn('filter_handle');


        $this->assertEquals(0, $filter->calculateAcceptedArgs());
    }

    /**
     * Test calculating accepted args count for handle.
     * Handle is function with arguments.
     *
     * @return void
     */
    public function testCalculatingAcceptedArgsForHandleFunctionWithArguments(): void
    {
        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->addMethods(['handle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturn('filter_handle_with_4_args');

        $this->assertEquals(4, $filter->calculateAcceptedArgs());
    }

    /**
     * Test calculating accepted args count for handle.
     * Handle is static class method without arguments.
     *
     * @see \Roots\Acorn\Tests\Unit\Support\Mocks\StaticFilterMock
     * @return void
     */
    public function testCalculatingAcceptedArgsForStaticHandleMethodWithoutArguments(): void
    {
        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturn(
            'Roots\Acorn\Tests\Unit\Support\Mocks\StaticFilterMock::staticHandle'
        );

        $this->assertEquals(0, $filter->calculateAcceptedArgs());
    }

    /**
     * Test calculating accepted args count for handle.
     * Handle is static class method with arguments.
     *
     * @see \Roots\Acorn\Tests\Unit\Support\Mocks\StaticFilterMock
     * @return void
     */
    public function testCalculatingAcceptedArgsForStaticHandleMethodWithArguments(): void
    {
        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturn(
            'Roots\Acorn\Tests\Unit\Support\Mocks\StaticFilterMock::staticHandleWith3Args'
        );

        $this->assertEquals(3, $filter->calculateAcceptedArgs());
    }


    /**
     * Test get handle throws type error
     * on return not callable.
     *
     * @return void
     */
    public function testGetHandleThrowsTypeErrorOnReturnNotCallable(): void
    {
        $this->expectException(\TypeError::class);

        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturnCallback(function () {
            return ['america', 'europe', 'africa'];
        });

        $filter->getHandle();
    }

    /**
     * Test get handle throws type error
     * on return not exists function.
     *
     * @return void
     */
    public function testGetHandleThrowsTypeErrorOnReturnNotExistsFunction(): void
    {
        $this->expectException(\TypeError::class);

        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturnCallback(function () {
            return 'arctica';
        });

        $filter->getHandle();
    }

    /**
     * Test get handle throws type error
     * on return not exists object method.
     *
     * @return void
     */
    public function testGetHandleThrowsTypeErrorOnReturnNotExistsObjectMethod(): void
    {
        $this->expectException(\TypeError::class);

        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturnCallback(function () use ($filter) {
            return [$filter, 'australia'];
        });

        $filter->getHandle();
    }

    /**
     * Test get handle throws type error
     * on return not exists class static method.
     *
     * @return void
     */
    public function testGetHandleThrowsTypeErrorOnReturnNotExistsStaticMethod(): void
    {
        $this->expectException(\TypeError::class);

        $filter = $this->createFilterMockBuilder()
            ->onlyMethods(['getHandle'])
            ->getMockForAbstractClass();

        $filter->method('getHandle')->willReturnCallback(function () {
            return 'Universe::milkyWay';
        });

        $filter->getHandle();
    }

    /**
     * Test get handle throws runtime exception
     * on not implemented default handle method.
     *
     * @return void
     */
    public function testGetHandleThrowsRuntimeExceptionOnNotImplementedDefaultHandleMethod(): void
    {
        $this->expectException(\RuntimeException::class);

        $filter = $this->createFilterMockBuilder()->getMockForAbstractClass();
        $filter->getHandle();
    }

    /**
     * Test get tag return iterable type.
     *
     * @return void
     */
    public function testGetTagReturnsIterableType(): void
    {
        $filter = $this->createFilterMockBuilder()->getMockForAbstractClass();

        $this->assertTrue(
            is_iterable($filter->getTag())
        );
    }

    /**
     * Test setter and getter for tag.
     *
     * @return void
     */
    public function testSetTagValidValueSuccessfully(): void
    {
        $filter = $this->createFilterMockBuilder()->getMockForAbstractClass();
        $tag = ['fist_hook', 'second', 'and_many_more'];

        $filter->setTag($tag);
        $this->assertEquals($tag, $filter->getTag());
    }

    /**
     * Test set tag throws type error
     * on invalid value.
     *
     * @return void
     */
    public function testSetTagThrowsTypeErrorOnInvalidValue(): void
    {
        $this->expectException(\TypeError::class);

        $filter = $this->createFilterMockBuilder()->getMockForAbstractClass();
        $tag = 'single_hook_as_string';

        $filter->setTag($tag);
    }

    /**
     * Create filter from abstract filter class.
     *
     * @return \PHPUnit\Framework\MockObject\MockBuilder
     */
    protected function createFilterMockBuilder(): MockBuilder
    {
        return $this->getMockBuilder(Filter::class)
            ->enableProxyingToOriginalMethods();
    }
}
