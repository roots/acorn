<?php

namespace Roots\Acorn\Tests\Unit\TestDouble;

use Psr\Container\ContainerInterface as PsrContainerInterface;

use function array_key_exists;

final class ContainerStub implements PsrContainerInterface
{
    /** @var array<string, mixed> */
    private $map;

    public function __construct(?array $map = null)
    {
        $this->map = $map ?? [];
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->map[$id] ?? null;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->map);
    }

    /**
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function set($id, $value): void
    {
        $this->map[$id] = $value;
    }
}
