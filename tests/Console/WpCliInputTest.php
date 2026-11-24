<?php

use Illuminate\Console\Command;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Kernel;
use Roots\Acorn\Console\WpCliInput;
use Roots\Acorn\Tests\Test\TestCase;
use Symfony\Component\Console\Input\StringInput;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

beforeEach(function () {
    $app = new Application(temp('wp_cli_input'));
    $app->instance('files', new Filesystem());

    $this->kernel = new Kernel($app, new Dispatcher($app));
    $this->kernel->command('json-only {--json}', fn () => 0);
    $this->kernel->command('json-and-format {--json} {--format=}', fn () => 0);
    $this->kernel->command('neither {--method=}', fn () => 0);
    $this->kernel->registerCommand(new class extends Command {
        protected $signature = 'aliased {--json}';

        protected $aliases = ['aliased:alias'];

        public function handle()
        {
            return 0;
        }
    });
});

it('passes --json to commands without a --format option', function (array $args) {
    $input = (new WpCliInput($this->kernel))->make($args, ['format' => 'json']);

    expect($input->hasParameterOption('--json'))->toBeTrue()->and($input->hasParameterOption('--format'))->toBeFalse();
})->with([
    'command' => [['json-only']],
    'alias' => [['aliased:alias']],
    'option before the command' => [['-n', 'json-only']],
]);

it('leaves --format alone', function (array $args, array $options) {
    $input = (new WpCliInput($this->kernel))->make($args, $options);

    expect($input->hasParameterOption('--json'))
        ->toBeFalse()
        ->and($input->getParameterOption('--format'))
        ->toBe($options['format']);
})->with([
    'command with both options' => [['json-and-format'], ['format' => 'json']],
    'command with neither option' => [['neither'], ['format' => 'json']],
    'other formats' => [['json-only'], ['format' => 'table']],
    'unknown command' => [['missing'], ['format' => 'json']],
    'abbreviated command' => [['json-on'], ['format' => 'json']],
    'no command' => [[], ['format' => 'json']],
]);

it('leaves --format alone when the command lookup fails', function () {
    $kernel = Mockery::mock(Kernel::class);
    $kernel->shouldReceive('findCommand')->andThrow(new RuntimeException('Unresolvable dependency'));

    $input = (new WpCliInput($kernel))->make(['json-only'], ['format' => 'json']);

    expect($input->getParameterOption('--format'))->toBe('json');
});

it('leaves --format alone when the kernel cannot find commands', function () {
    $input = (new WpCliInput(Mockery::mock(Illuminate\Contracts\Console\Kernel::class)))->make(['json-only'], [
        'format' => 'json',
    ]);

    expect($input->getParameterOption('--format'))->toBe('json');
});

it('builds the input from WP-CLI arguments', function (array $args, array $options, string $expected) {
    expect((string) (new WpCliInput($this->kernel))->make($args, $options))->toBe((string) new StringInput($expected));
})->with([
    'arguments' => [['make:model', 'Post Type'], [], "'make:model' 'Post Type'"],
    'options' => [['neither'], ['method' => 'GET POST', 'force' => true], "'neither' --method='GET POST' --force"],
    'no interaction' => [['neither'], ['interaction' => false], "'neither' --no-interaction"],
    'backslashes' => [['make:model', 'App\Models\Post'], [], "'make:model' 'App\\\\Models\\\\Post'"],
]);
