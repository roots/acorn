<?php

namespace Roots\Acorn\View;

use Illuminate\View\View;
use Illuminate\Support\Str;

abstract class Composer
{
    protected static $views;

    /**
     * List of views served by this composer
     *
     * @return string|string[]
     */
    public static function views()
    {
        if (static::$views) {
            return static::$views;
        }

        $view = array_slice(explode('\\', static::class), 2);
        $view = array_map([Str::class, 'snake'], $view, array_fill(0, count($view), '-'));
        return implode('/', $view);
    }

    /**
     * Compose the view before rendering.
     *
     * @param \Illuminate\View\View $view;
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(array_merge(
            $this->with($view->getData(), $view),
            $view->getData(),
            $this->override($view->getData(), $view)
        ));
    }

    /**
     * Data to be passed to view before rendering
     *
     * @param array $data
     * @param \Illuminate\View\View $view
     * @return array
     */
    public function override($data = [], $view = null)
    {
        return [];
    }

    /**
     * Data to be passed to view before rendering
     *
     * @param array $data
     * @param \Illuminate\View\View $view
     * @return array
     */
    public function with($data = [], $view = null)
    {
        return [];
    }
}
