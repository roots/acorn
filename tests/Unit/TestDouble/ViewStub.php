<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Illuminate\Contracts\View\View;

final class ViewStub implements View
{
    /** @var array<string, mixed> */
    private $map = [];
    /** @var string */
    private $content;
    /** @var string */
    private $name;

    public function __construct(string $name = '', string $content = '')
    {
        $this->name = $name;
        $this->content = $content;
    }

    public function render()
    {
        return $this->content;
    }

    public function name()
    {
        return $this->name;
    }

    public function with($key, $value = null)
    {
        $this->map[$key] = $value;
        return $this;
    }

    public function getData(): array
    {
        return $this->map;
    }
}
