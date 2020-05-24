<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Roots\Acorn\Clover\Meta;
use Roots\Acorn\Clover\ServiceProvider;

final class CloverServiceProviderStub extends ServiceProvider
{
    public function __construct($app, Meta $meta)
    {
        parent::__construct($app);
        $this->meta = $meta;
    }
}
