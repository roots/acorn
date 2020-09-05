<?php

namespace Roots\Acorn\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component as ComponentBase;

use function Roots\view;

abstract class Component extends ComponentBase
{
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|null  $view
     * @param  Arrayable|array  $data
     * @param  array  $mergeData
     * @return View|Factory
     */
    public function view($view = null, $data = [], $mergeData = [])
    {
        return view($view, $data, $mergeData);
    }
}
