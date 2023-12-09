<?php

namespace Roots\Acorn\View\Composers\Concerns;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

trait Arrayable
{
    /**
     * Maps available class methods to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return collect((new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(fn ($method) => $this->shouldIgnore($method->name))
            ->mapWithKeys(function ($method) {
                $data = $this->{$method->name}();

                return [Str::snake($method->name) => is_array($data) ? new Fluent($data) : $data];
            })
            ->all();
    }
}
