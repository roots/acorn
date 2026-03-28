<?php

use Roots\Acorn\Assets\View\BladeDirective;
use Roots\Acorn\Tests\Test\TestCase;

use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

it('loads an asset', function () {
    $directive = new BladeDirective();
    assertMatchesSnapshot($directive("'kjo'"));
});
