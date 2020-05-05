<?php

namespace Roots\Acorn\View;

use Illuminate\Support\Str;
use Illuminate\Support\Fluent;
use Illuminate\View\View;

abstract class Composer
{
    /**
     * List of views to receive data by this composer
     *
     * @var string[]
     */
    protected static $views;

    /**
     * Current view
     *
     * @var \Illuminate\View\View
     */
    protected $view;

    /**
     * Current view data
     *
     * @var \Illuminate\Support\Fluent
     */
    protected $data;

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

        $view = array_slice(explode('\\', static::class), 3);
        $view = array_map([Str::class, 'snake'], $view, array_fill(0, count($view), '-'));
        return implode('/', $view);
    }

    /**
     * Compose the view before rendering.
     *
     * @param  \Illuminate\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        $this->view = $view;
        $this->data = new Fluent($view->getData());

        $view->with($this->merge());
    }

    /**
     * Data to be merged and passed to the view before rendering.
     *
     * @return array
     */
    protected function merge()
    {
        return array_merge(
            $this->with(),
            $this->view->getData(),
            $this->override()
        );
    }

    /**
     * Data to be passed to view before rendering
     *
     * @return array
     */
    protected function with()
    {
        return [];
    }

    /**
     * Data to be passed to view before rendering
     *
     * @return array
     */
    protected function override()
    {
        return [];
    }
}
