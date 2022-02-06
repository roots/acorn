<?php

use Roots\Acorn\Bootloader;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

it('should get a new instance', function () {
    expect(Bootloader::getInstance())->toBeInstanceOf(Bootloader::class);
});
