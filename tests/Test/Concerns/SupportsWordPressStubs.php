<?php

namespace Roots\Acorn\Tests\Test\Concerns;

use function Roots\Acorn\Tests\temp;

trait SupportsWordPressStubs
{
    /**
     * WordPress filters
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Adds WordPress globals that are frequently used.
     */
    protected function wordPressStubs(): void
    {
        $this->stubs([
            'apply_filters' => fn (...$args) => $this->apply(...$args),
            'add_filter' => fn (...$args) => $this->filter(...$args),
            '__return_true' => fn () => true,
            '__return_false' => fn () => false,
            '__return_zero' => fn () => 0,
            '__return_empty_array' => fn () => [],
            '__return_empty_string' => fn () => '',
            'locate_template' => fn ($path = null) => temp($path ?? 'locate_template'),
            'get_stylesheet_directory' => fn ($path = null) => temp($path ?? 'stylesheet_directory'),
            'get_template_directory' => fn ($path = null) => temp($path ?? 'template_directory'),
            'get_theme_file_path' => fn ($path = null) => temp($path ?? 'theme_file_path'),
            'do_action' => fn (...$args) => !! $this->apply(...$args),
            'doing_action',
            'did_action',
            'add_action' => fn (...$args) => $this->filter(...$args),
        ]);

        $this->filters = [];
    }

    protected function apply($key, $initial = null, ...$args)
    {
        return array_reduce(
            $this->filters[$key] ?? [],
            fn ($value, $callable) => call_user_func_array($callable, array_merge([$value], $args)),
            $initial
        );
    }

    protected function filter($key, $fn, $priority = 10)
    {
        do {
            $priority .= 0;
        } while (isset($this->filters[$key][$priority]));

        $this->filters[$key][$priority] = $fn;

        ksort($this->filters[$key]);

        return true;
    }
}
