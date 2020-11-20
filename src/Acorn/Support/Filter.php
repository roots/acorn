<?php

namespace Roots\Acorn\Support;

use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use Roots\Acorn\Support\Contracts\Filter as Contract;

use function Roots\add_filters as add_filters;
use function Roots\remove_filters as remove_filters;

abstract class Filter implements Contract
{
    /**
     * Handle method name.
     *
     * @var string
     */
    protected $handleMethodName = 'handle';

    /**
     * Filter priority when registered with the provider.
     *
     * @var int
     */
    protected $priority = 10;

    /**
     * The filter tag it responds to.
     *
     * @var iterable
     */
    protected $tag = 'init';

    /**
     * Apply filter in system.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function apply(): void
    {
        add_filters(
            $this->getTag(),
            $this->getHandle(),
            $this->getPriority(),
            $this->calculateAcceptedArgs()
        );
    }

    /**
     * Get accepted arguments number.
     * The number of accepted arguments is automatically counted
     * by reflection mechanism.
     *
     * @return int
     * @throws \ReflectionException
     */
    public function calculateAcceptedArgs(): int
    {
        // get handle callback
        $handle = $this->getHandle();

        // check is static method call in string
        // eg. MyClass::myCallbackMethod
        $methodCallPosition = is_string($handle) ? strpos($handle, '::') : false;
        $isStaticMethod = $methodCallPosition !== false;

        // check handle if object method call
        if (is_array($handle) || $isStaticMethod) {
            // if is static method call
            // we need to divide string to classname and method
            if ($isStaticMethod) {
                // if is static method call
                // we need to divide string to classname and method

                $handle = [
                    substr($handle, 0, $methodCallPosition), // classname
                    substr($handle, ($methodCallPosition + 2)) // method
                ];
            }

            // get method arguments count
            $reflectionMethod = new ReflectionMethod($handle[0], $handle[1]);
            return count(
                $reflectionMethod->getParameters()
            );
        }

        // get function arguments count
        $reflectionFunction = new ReflectionFunction($handle);
        return count(
            $reflectionFunction->getParameters()
        );
    }

    /**
     * Get filter handle method.
     *
     * @return callable
     * @throws \RuntimeException
     */
    public function getHandle(): callable
    {
        // by default handle method is $this->handle()
        if (!method_exists($this, $this->handleMethodName)) {
            throw new RuntimeException(
                sprintf(
                    'The filter [%s] does not have method [%s] which is handle.',
                    get_class($this),
                    $this->handleMethodName
                )
            );
        }

        return [$this, $this->handleMethodName];
    }

    /**
     * Get filter priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Get filter tag.
     *
     * @return iterable
     */
    public function getTag(): iterable
    {
        return $this->tag;
    }

    /**
     * Set filter priority.
     *
     * @param int $priority
     *
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Set filter tag.
     *
     * @param iterable $tag
     *
     * @return void
     */
    public function setTag(iterable $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * Remove filter from system.
     *
     * @return void
     */
    public function remove(): void
    {
        remove_filters(
            $this->getTag(),
            $this->getHandle(),
            $this->getPriority()
        );
    }
}
