<?php

use Illuminate\Config\Repository;
use Illuminate\Console\Application as Artisan;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\ViewClearCommand;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Commands\ViewCacheCommand;
use Roots\Acorn\Tests\Test\TestCase;
use Roots\Acorn\View\ViewServiceProvider;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

beforeEach(function () {
    $this->views = ['greeting', 'partials.item'];

    $this->makeApp = function (string $base, string $compiled, array $config = []) {
        $files = new Filesystem();

        if (! $files->isDirectory("{$base}/resources/views")) {
            $files->copyDirectory(dirname(__DIR__) . '/View/__fixtures__/views', "{$base}/resources/views");
            $files->ensureDirectoryExists("{$base}/package");
            $files->put("{$base}/package/widget.blade.php", '<div>Widget</div>');
        }

        $app = new Application($base);
        $app->instance('config', new Repository([
            'view' => [
                'paths' => ["{$base}/resources/views"],
                'compiled' => $compiled,
                'namespaces' => ['package' => "{$base}/package"],
                ...$config,
            ],
        ]));
        $app->instance('events', new Dispatcher($app));
        $app->instance('files', $files);
        $app->register(ViewServiceProvider::class);

        return $app;
    };

    $this->cache = function (Application $app) {
        $artisan = new Artisan($app, new Dispatcher($app), 'testing');
        $artisan->setAutoExit(false);
        $artisan->add($app->make(ViewClearCommand::class));
        $artisan->add(new ViewCacheCommand());

        return $artisan->run(new StringInput('view:cache'), new BufferedOutput());
    };

    $this->compiledFiles = function (string $path) {
        clearstatcache();

        return collect(glob("{$path}/*"))
            ->mapWithKeys(fn ($file) => [basename($file) => filemtime($file) . md5_file($file)])
            ->sortKeys()
            ->all();
    };
});

it('creates a loader for each view', function () {
    $compiled = temp('view_cache/' . uniqid()) . '/views';
    mkdir($compiled);

    $app = ($this->makeApp)(temp('view_cache/' . uniqid()), $compiled);

    expect(($this->cache)($app))->toBe(0);

    $loaders = glob("{$compiled}/*-loader.php");

    expect($loaders)->toHaveCount(count($this->views));

    foreach ($this->views as $name) {
        expect($app['view']->make($name)->makeLoader())->toBeIn($loaders);
    }

    expect($app['view']->make('package::widget')->getCompiled())->toBeFile();
});

it('renders cached views and reuses loaders without writing after a move', function (
    bool $relativeHash,
    bool $moveBase,
) {
    $buildBase = temp('view_cache/' . uniqid());
    $buildCompiled = temp('view_cache/' . uniqid()) . '/views';
    mkdir($buildCompiled);

    $config = ['relative_hash' => $relativeHash];

    expect(($this->cache)(($this->makeApp)($buildBase, $buildCompiled, $config)))->toBe(0);

    $base = $moveBase ? temp('view_cache/' . uniqid()) : $buildBase;
    $compiled = temp('view_cache/' . uniqid()) . '/views';
    (new Filesystem())->copyDirectory($buildCompiled, $compiled);

    // Backdate the copies so a recompile would change their modification times.
    array_map(fn ($file) => touch($file, time() - 3600), glob("{$compiled}/*"));

    $cached = ($this->compiledFiles)($compiled);

    $app = ($this->makeApp)($base, $compiled, [...$config, 'check_cache_timestamps' => false]);

    foreach (['greeting' => '<p>Hello Acorn</p>', 'partials.item' => '<li>Acorn</li>'] as $name => $html) {
        $view = $app['view']->make($name, ['name' => 'Acorn', 'item' => 'Acorn']);

        expect($view->render())->toContain($html)->and($view->makeLoader())->toBeFile();
    }

    expect(($this->compiledFiles)($compiled))->toBe($cached);
})->with([
    'compiled path' => [false, false],
    'base and compiled paths with relative hashes' => [true, true],
]);
