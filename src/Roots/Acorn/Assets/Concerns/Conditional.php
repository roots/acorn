<?php

namespace Roots\Acorn\Assets\Concerns;

trait Conditional
{
    /**
     * Conditionally load assets.
     *
     * @var bool
     */
    protected $conditional = true;

    /**
     * Set conditional loading.
     *
     * @param bool|callable $conditional
     * @return $this
     */
    public function when($conditional, ...$args)
    {
        $this->conditional = is_callable($conditional)
            ? call_user_func($conditional, $args)
            : $conditional;

        return $this;
    }
}
