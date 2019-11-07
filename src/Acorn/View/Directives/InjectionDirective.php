<?php

namespace Roots\Acorn\View\Directives;

use Illuminate\View\Compilers\Concerns\CompilesInjections;

class InjectionDirective
{
    use CompilesInjections;

    /**
     * Inject directives with the Application namespace.
     *
     * @param  string $expression
     * @return string
     */
    public function __invoke($expression)
    {
        return str_replace(' = app(\'', ' = \\Roots\\app(\'', $this->compileInject($expression));
    }
}
