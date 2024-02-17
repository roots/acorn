<?php

namespace Roots\Acorn\View;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Roots\Acorn\View\Composers\Concerns\Extractable;

abstract class Composer
{
    use Extractable;

    /**
     * The list of views served by this composer.
     *
     * @var string[]
     */
    protected static $views;

    /**
     * The current view instance.
     *
     * @var \Illuminate\View\View
     */
    protected $view;

    /**
     * The current view data.
     *
     * @var \Illuminate\Support\Fluent
     */
    protected $data;

    /**
     * The properties / methods that should not be exposed.
     *
     * @var array
     */
    protected $except = [];

    /**
     * The default properties / methods that should not be exposed.
     *
     * @var array
     */
    protected $defaultExcept = [
        'cache',
        'compose',
        'override',
        'toArray',
        'views',
        'with',
    ];

    /**
     * The list of views served by this composer.
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
     * @return void
     */
    public function compose(View $view)
    {
        $this->view = $view;
        $this->data = new Fluent($view->getData());

        $view->with($this->merge());
    }

    /**
     * The merged data to be passed to view before rendering.
     *
     * @return array
     */
    protected function merge()
    {
        [$with, $override] = [$this->with(), $this->override()];

        if (! $with && ! $override) {
            return array_merge(
                $this->extractPublicProperties(),
                $this->extractPublicMethods(),
                $this->view->getData()
            );
        }

        return array_merge(
            $with,
            $this->view->getData(),
            $override
        );
    }

    /**
     * The data passed to the view before rendering.
     *
     * @return array
     */
    protected function with()
    {
        return [];
    }

    /**
     * The override data passed to the view before rendering.
     *
     * @return array
     */
    protected function override()
    {
        return [];
    }

    /**
     * Determine if the given property / method should be ignored.
     *
     * @param  string  $name
     * @return bool
     */
    protected function shouldIgnore($name)
    {
        return str_starts_with($name, '__') ||
            in_array($name, $this->ignoredMethods());
    }

    /**
     * Get the methods that should be ignored.
     *
     * @return array
     */
    protected function ignoredMethods()
    {
        return array_merge($this->defaultExcept, $this->except);
    }
}
