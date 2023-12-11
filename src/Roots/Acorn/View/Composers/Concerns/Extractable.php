<?php

namespace Roots\Acorn\View\Composers\Concerns;

use Closure;
use Illuminate\View\InvokableComponentVariable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Extract public properties from a composer to make available in a view.
 *
 * @copyright Taylor Otwell
 *
 * @link https://github.com/illuminate/view/blob/v10.35.0/Component.php#L220-L342
 */
trait Extractable
{
    /**
     * The cache of public property names, keyed by class.
     *
     * @var array
     */
    protected static $propertyCache = [];

    /**
     * The cache of public method names, keyed by class.
     *
     * @var array
     */
    protected static $methodCache = [];

    /**
     * Extract the public properties for the class.
     *
     * @return array
     */
    protected function extractPublicProperties()
    {
        $class = get_class($this);

        if (! isset(static::$propertyCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$propertyCache[$class] = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
                ->reject(fn (ReflectionProperty $property) => $property->isStatic() || $this->shouldIgnore($property->getName()))
                ->map(fn (ReflectionProperty $property) => $property->getName())
                ->all();
        }

        $values = [];

        foreach (static::$propertyCache[$class] as $property) {
            $values[$property] = $this->{$property};
        }

        return $values;
    }

    /**
     * Extract the public methods for the class.
     *
     * @return array
     */
    protected function extractPublicMethods()
    {
        $class = get_class($this);

        if (! isset(static::$methodCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$methodCache[$class] = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(fn (ReflectionMethod $method) => $this->shouldIgnore($method->getName()))
                ->map(fn (ReflectionMethod $method) => $method->getName());
        }

        $values = [];

        foreach (static::$methodCache[$class] as $method) {
            $values[$method] = $this->createVariableFromMethod(new ReflectionMethod($this, $method));
        }

        return $values;
    }

    /**
     * Create a callable variable from the given method.
     *
     * @return mixed
     */
    protected function createVariableFromMethod(ReflectionMethod $method)
    {
        return $method->getNumberOfParameters() === 0
            ? $this->createInvokableVariable($method->getName())
            : Closure::fromCallable([$this, $method->getName()]);
    }

    /**
     * Create an invokable, toStringable variable for the given class method.
     *
     * @return \Illuminate\View\InvokableComponentVariable
     */
    protected function createInvokableVariable(string $method)
    {
        return new InvokableComponentVariable(fn () => $this->{$method}());
    }
}
