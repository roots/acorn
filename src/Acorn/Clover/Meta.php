<?php

namespace Roots\Acorn\Clover;

use Illuminate\Support\Arr;
use Illuminate\Config\Repository;

class Meta extends Repository
{
    /**
     * Disable the set() method because Meta is immutable.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return \trigger_error
     */
    public function set($key, $value = null)
    {
        \trigger_error(sprintf(
            __('%s is immutable and cannot be modified after instantiation.', 'acorn'),
            __CLASS__
        ), E_USER_ERROR);
    }

    /**
     * Allow object notation.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        $value = $this->get($key);

        if (is_array($value) && Arr::isAssoc($value)) {
            return new static($value);
        }

        return $value;
    }
}
