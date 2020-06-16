<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

use function array_keys;
use function array_merge;
use function array_values;

final class ViewFactoryStub implements Factory
{
    /**
     * @var array<string, array<string, string>>
     */
    private $map = [];
    /**
     * @var array<string, mixed>
     */
    private $shared = [];

    public function exists($view): bool
    {
        return false;
    }

    public function file($path, $data = [], $mergeData = []): View
    {
        return new ViewStub();
    }

    public function make($view, $data = [], $mergeData = []): View
    {
        return new ViewStub();
    }

    public function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->shared[$key] = $value;
        }

        return $value;
    }

    public function composer($views, $callback): array
    {
        return [];
    }

    public function creator($views, $callback): array
    {
        return [];
    }

    public function addNamespace($namespace, $hints): self
    {
        $existingHints = $this->map[$namespace] ?? [];
        $this->map[$namespace] = array_merge($hints, $existingHints);
        return $this;
    }

    public function replaceNamespace($namespace, $hints): self
    {
        $this->map[$namespace] = $hints;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRegisteredNamespaces(): array
    {
        return array_keys($this->map);
    }

    /**
     * @return string[]
     */
    public function getPathsForNamespace(string $namespace): array
    {
        return array_values($this->map[$namespace] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getShared(): array
    {
        return $this->shared;
    }
}
