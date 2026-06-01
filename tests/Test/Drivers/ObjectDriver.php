<?php

namespace Roots\Acorn\Tests\Test\Drivers;

use Spatie\Snapshots\Drivers\ObjectDriver as BaseObjectDriver;

/**
 * Snapshot driver that normalizes empty inline collections.
 *
 * symfony/yaml renders an empty array inline as `{  }` on v7 and `{}` on v8,
 * which the resolved version depends on the PHP version under test (v8
 * requires PHP >= 8.4). Normalizing keeps snapshots stable across both.
 */
class ObjectDriver extends BaseObjectDriver
{
    public function serialize($data): string
    {
        return preg_replace('/\{ +\}/', '{}', parent::serialize($data));
    }
}
