<?php

namespace Roots\Acorn\Exceptions;

use InvalidArgumentException;
use Throwable;

class SkipProviderException extends InvalidArgumentException
{
    /**
     * Create a new exception.
     *
     * @return void
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, string $package = '')
    {
        parent::__construct($message, $code, $previous);

        $this->package = $package;
    }

    /**
     * Name of the provider's package.
     *
     * @var string
     */
    protected $package;

    /**
     * Set the name of the provider's package.
     *
     * @return void
     */
    public function setPackage(string $package)
    {
        $this->package = $package;
    }

    /**
     * Get the provider's package.
     *
     * @return string
     */
    public function package()
    {
        return $this->package;
    }

    /**
     * Report the exception.
     *
     * @return array
     */
    public function context()
    {
        return [
            'package' => $this->package(),
        ];
    }
}
