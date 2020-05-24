<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Roots\Acorn\Clover\Concerns\Lifecycle;
use Roots\Acorn\Clover\Meta;

final class PluginStub
{
    use Lifecycle;

    /** @var Meta */
    private $meta;

    private $runWasCalled = false;
    private $lifecycleWasCalled = false;

    public function __construct(Meta $meta)
    {
        $this->meta = $meta;
    }

    public function run()
    {
        $this->runWasCalled = true;
    }

    public function runWasCalled(): bool
    {
        return $this->runWasCalled;
    }

    public function lifecycle(Meta $meta): void
    {
        $this->lifecycleWasCalled = true;
    }

    public function lifecycleWasCalled(): bool
    {
        return $this->lifecycleWasCalled;
    }
}
