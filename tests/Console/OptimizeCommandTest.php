<?php

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Events\Dispatcher;
use Psr\Log\NullLogger;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Commands\OptimizeClearCommand;
use Roots\Acorn\Console\Commands\OptimizeCommand;
use Roots\Acorn\Tests\Test\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

uses(TestCase::class);

beforeEach(function () {
    $this->app = new Application();
    $this->app->instance('log', new NullLogger());

    $this->artisan = new Artisan($this->app, new Dispatcher($this->app), 'testing');
    $this->artisan->setAutoExit(false);

    $this->called = [];

    $this->registerCommands = function (array $names) {
        foreach ($names as $name) {
            $command = new class extends Command {
                public $called;

                public function handle()
                {
                    ($this->called)($this->getName());
                }
            };

            $command->setName($name);
            $command->called = fn ($name) => $this->called[] = $name;

            $this->artisan->add($command);
        }
    };

    $this->output = new BufferedOutput();

    $this->run = fn (string $input) => $this->artisan->run(new StringInput($input), $this->output);
});

it('runs every optimize task', function () {
    ($this->registerCommands)(['config:cache', 'event:cache', 'route:cache', 'view:cache']);
    $this->artisan->add(new OptimizeCommand());

    expect(($this->run)('optimize'))
        ->toBe(0)
        ->and($this->called)
        ->toBe(['config:cache', 'event:cache', 'route:cache', 'view:cache']);
});

it('skips optimize tasks passed to --except by key or command name', function () {
    ($this->registerCommands)(['config:cache', 'event:cache', 'route:cache', 'view:cache']);
    $this->artisan->add(new OptimizeCommand());

    expect(($this->run)('optimize --except=views,route:cache'))
        ->toBe(0)
        ->and($this->called)
        ->toBe(['config:cache', 'event:cache']);
});

it('accepts the short --except option with whitespace around values', function () {
    ($this->registerCommands)(['config:cache', 'event:cache', 'route:cache', 'view:cache']);
    $this->artisan->add(new OptimizeCommand());

    expect(($this->run)('optimize -e " views , route:cache "'))
        ->toBe(0)
        ->and($this->called)
        ->toBe(['config:cache', 'event:cache'])
        ->and($this->output->fetch())
        ->toContain('config', 'events')
        ->not->toContain('routes', 'views');
});

it('gracefully skips optimize tasks for commands that are not registered', function () {
    ($this->registerCommands)(['config:cache', 'view:cache']);
    $this->artisan->add(new OptimizeCommand());

    expect(($this->run)('optimize'))->toBe(0)->and($this->called)->toBe(['config:cache', 'view:cache']);
});

it('skips optimize:clear tasks passed to --except', function () {
    ($this->registerCommands)([
        'cache:clear',
        'clear-compiled',
        'config:clear',
        'event:clear',
        'route:clear',
        'view:clear',
    ]);
    $this->artisan->add(new OptimizeClearCommand());

    expect(($this->run)('optimize:clear --except=cache,events'))
        ->toBe(0)
        ->and($this->called)
        ->toBe(['config:clear', 'clear-compiled', 'route:clear', 'view:clear']);
});

it('gracefully skips optimize:clear tasks for commands that are not registered', function () {
    ($this->registerCommands)(['config:clear', 'view:clear']);
    $this->artisan->add(new OptimizeClearCommand());

    expect(($this->run)('optimize:clear'))->toBe(0)->and($this->called)->toBe(['config:clear', 'view:clear']);
});
