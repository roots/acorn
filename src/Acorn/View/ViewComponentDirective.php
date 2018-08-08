<?php

namespace Roots\Acorn\View;

use Roots\Acorn\Application;

class ViewComponentDirective
{
    public function __construct(Application $app, ComponentFinder $finder)
    {
        $this->app = $app;
        $this->finder = $finder;
    }

    public function __invoke($expression)
    {
        return "<?= {$expression}; ?>";
        return var_export($component, true) . '|' . var_export($data, true);
    }
}
