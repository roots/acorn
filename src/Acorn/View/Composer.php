<?php

namespace Roots\Acorn\View;

use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class Composer
{
    /** @var string[] List of views to receive data by this composer */
    protected static $views;

    /** @var \Illuminate\View\View Current view */
    protected $view;

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
        $this->view = $view;

        $view->with($this->getData());
    }

    /**
     * Data to be passed to view before rendering
     *
     * @return array
     */
    protected function getData()
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
