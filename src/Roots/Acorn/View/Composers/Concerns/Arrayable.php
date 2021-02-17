<?php

namespace Roots\Acorn\View\Composers\Concerns;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

trait Arrayable
{
    /**
     * Ignored Methods
     *
     * @var string[]
     */
    protected $ignore = [];

    /**
     * Maps available class methods to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return collect((new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(function ($method) {
                return ! in_array($method->name, array_merge(
                    $this->ignore,
                    ['compose', 'toArray', 'with', 'views', 'override']
                ));
            })
            ->filter(function ($method) {
                return ! Str::startsWith($method->name, ['__', 'cache']);
            })
            ->mapWithKeys(function ($method) {
                $data = $this->{$method->name}();
                return [Str::snake($method->name) => is_array($data) ? new Fluent($data) : $data];
            })
            ->all();
    }
}
