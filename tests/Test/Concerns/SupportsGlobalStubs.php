<?php

namespace Roots\Acorn\Tests\Test\Concerns;

use Akamon\MockeryCallableMock\MockeryCallableMock;

trait SupportsGlobalStubs
{
    public static $globals;

    /**
     * Create stubs and spies for global functions.
     *
     * @param string        $fn       The name of the global function
     * @param null|callable $callable The real function to monitor
     */
    protected function stub(string $fn, ?callable $callable = null): MockeryCallableMock
    {
        $script = <<<DECLARE_FUNCTION
        if (! function_exists('%1\$s')) {
            function %1\$s (...\$args) {
                return call_user_func_array(%2\$s::\$globals['%1\$s'], \$args);
            }
        }
        DECLARE_FUNCTION;

        eval(sprintf($script, $fn, __CLASS__));

        return self::$globals[$fn] = new MockeryCallableMock($callable);
    }

    /**
     * Create multiple stubs and spies for global functions.
     *
     * @param iterable $stubs
     * @return MockeryCallableMock[]
     */
    protected function stubs(iterable $callables): array
    {
        $stubs = [];

        foreach ($callables as $fn => $callable) {

            if (is_int($fn) && is_string($callable)) {
                $fn = $callable;
                $callable = null;
            }

            $stubs[$fn] = $this->stub($fn, $callable);
        }

        return $stubs;
    }

    /**
     * Clear all current global stubs.
     */
    protected function clearStubs(): void
    {
        self::$globals = [];
    }
}
