<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Roots\Acorn\Assets\Contracts\Asset;
use Roots\Acorn\Assets\Contracts\Manifest;
use RuntimeException;

final class ManifestSpy implements Manifest
{
    /** @var array<string, mixed> */
    private $map;
    /** @var array<string, int> */
    private $callLog;

    public function __construct(?array $map = null)
    {
        $this->map = $map ?? [];
    }

    public function get($key): Asset
    {
        $asset = $this->map[$key] ?? null;
        if ($asset === null) {
            throw new RuntimeException(sprintf('Manifest for key "%s" not found', $key));
        }
        return $asset;
    }

    public function set(string $key, Asset $value): void
    {
        $this->map[$key] = $value;
    }

    public function __call($name, $arguments): void
    {
        $this->callLog[$name] = ($this->callLog[$name] ?? 0) + 1;
    }

    /**
     * @return array<string, int>
     */
    public function getCallLog(): array
    {
        return $this->callLog;
    }
}
