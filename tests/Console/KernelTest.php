<?php

use Illuminate\Console\Scheduling\SchedulePauseCommand;
use Illuminate\Console\Scheduling\ScheduleResumeCommand;
use Illuminate\Console\Signals;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Kernel;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Signals::resolveAvailabilityUsing(null);

    $this->app = new Application();

    $this->signalsEnabled = function (): bool {
        $ran = false;

        Signals::whenAvailable(function () use (&$ran) {
            $ran = true;
        });

        return $ran;
    };
});

afterEach(function () {
    Signals::resolveAvailabilityUsing(null);
});

it('enables signals in the console when pcntl is available', function () {
    $this->app['env'] = 'production';

    new Kernel($this->app, new Dispatcher($this->app));

    expect(($this->signalsEnabled)())->toBe(extension_loaded('pcntl'));
});

it('disables signals when running unit tests', function () {
    $this->app['env'] = 'testing';

    new Kernel($this->app, new Dispatcher($this->app));

    expect(($this->signalsEnabled)())->toBeFalse();
});

it('registers schedule:pause and schedule:resume', function () {
    $this->app->instance('files', new Filesystem());

    $kernel = new Kernel($this->app, new Dispatcher($this->app));

    expect($kernel->findCommand('schedule:pause'))
        ->toBeInstanceOf(SchedulePauseCommand::class)
        ->and($kernel->findCommand('schedule:resume'))
        ->toBeInstanceOf(ScheduleResumeCommand::class)
        ->and($kernel->findCommand('schedule:continue'))
        ->toBeInstanceOf(ScheduleResumeCommand::class);
});
