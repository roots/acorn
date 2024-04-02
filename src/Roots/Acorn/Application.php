<?php

namespace Roots\Acorn;

use Roots\Acorn\Application\Application as BaseApplication;

/**
 * @deprected Use Roots\Acorn\Application\Application instead
 */
class Application extends BaseApplication
{
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);

        $this->booted(function () {
            // TODO: uncomment this line when we are ready to deprecate this class
            // $this->make('log')->debug(self::class.' is deprecated. Use '.BaseApplication::class.' instead.');
        });
    }
}
