<?php

use Illuminate\Container\Attributes\Bind;
use Roots\Acorn\Application;
use Roots\Acorn\Bootstrap\LoadConfiguration;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

#[Bind(ArrayObject::class)]
interface LoadConfigurationTestContract {}

it('resolves #[Bind] attributes', function () {
    $app = new Application(temp('load_configuration'));
    $app->useConfigPath($this->fixture('config'));
    $app->dontMergeFrameworkConfiguration();

    (new LoadConfiguration())->bootstrap($app);

    expect($app->make(LoadConfigurationTestContract::class))->toBeInstanceOf(ArrayObject::class);
});
