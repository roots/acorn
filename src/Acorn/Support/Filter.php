<?php

namespace Roots\Acorn\Support;

use Roots\Acorn\Support\Contracts\Filter as Contract;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;

use function Roots\add_filters as add_filters;

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
            $this->getAcceptedArgs()
        );
    }

    /**
     * Get accepted arguments number.
     * The number of accepted arguments is automatically counted by ReflectionMethod.
     *
     * @return int
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function getAcceptedArgs(): int
    {
        // get handle callback
        $handle = $this->getHandle();

        // check is static method call in string
        // eg. MyClass::myCallbackMethod
        $methodCallPosition = is_string($handle) ? strpos($handle, '::') : false;
        $isStaticMethod = $methodCallPosition !== false;

        // check handle if object method call
        if (is_array($handle) || $isStaticMethod) {
            // object method call must have exactly two parameters
            if (!$isStaticMethod && ($parametersCount = count($handle)) != 2) {
                throw new RuntimeException(
                    sprintf(
                        'Object method call must have exactly 2 parameters. Now have [%d].',
                        $parametersCount
                    )
                );
            }

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
     * @throw \RuntimeException When handle method does not exist.
     */
    public function getHandle(): callable
    {
        // by default handle method is $this->handle()
        if (!method_exists($this, $this->handleMethodName)) {
            throw new RuntimeException(
                sprintf(
                    'The filter [%s] must implement a method [%s].',
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
}
