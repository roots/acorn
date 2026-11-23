<?php

use Illuminate\Config\Repository;
use Illuminate\Console\Application as Artisan;
use Illuminate\Container\Container;
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
    $this->views = ['greeting', 'partials.item', 'theme'];

    $this->multisite = false;
    $this->stub('is_multisite', fn () => $this->multisite);

    // A WordPress install with a theme at `[root]/content/themes/[theme]`.
    $this->theme = function (string $root, string $theme = 'sage') {
        $files = new Filesystem();
        $base = "{$root}/content/themes/{$theme}";

        if (! $files->isDirectory("{$base}/resources/views")) {
            $files->copyDirectory(dirname(__DIR__) . '/View/__fixtures__/views', "{$base}/resources/views");
            $files->put("{$base}/resources/views/theme.blade.php", "<p>{$theme}</p>");
            $files->ensureDirectoryExists("{$base}/package");
            $files->put("{$base}/package/widget.blade.php", '<div>Widget</div>');
        }

        return $base;
    };

    $this->makeApp = function (string $base, string $compiled, array $config = []) {
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
        $app->instance('files', new Filesystem());

        $provider = new class($app) extends ViewServiceProvider {
            public ?string $content = null;

            protected function contentPath(): ?string
            {
                return $this->content;
            }
        };

        $provider->content = dirname($base, 2);

        $app->register($provider);

        return $app;
    };

    // Blade's engine resolves the compiler from the global container.
    $this->cache = function (Application $app) {
        Container::setInstance($app);

        $artisan = new Artisan($app, new Dispatcher($app), 'testing');
        $artisan->setAutoExit(false);
        $artisan->add($app->make(ViewClearCommand::class));
        $artisan->add(new ViewCacheCommand());

        return $artisan->run(new StringInput('view:cache'), new BufferedOutput());
    };

    $this->compiledDir = function () {
        $path = temp('view_cache/' . uniqid()) . '/views';
        mkdir($path);

        return $path;
    };

    $this->compiledFiles = function (string $path) {
        clearstatcache();

        return collect(glob("{$path}/*"))
            ->mapWithKeys(fn ($file) => [basename($file) => filemtime($file) . md5_file($file)])
            ->sortKeys()
            ->all();
    };

    // Backdate compiled files so a recompile would change their modification times.
    $this->backdate = fn (string $path) => array_map(fn ($file) => touch($file, time() - 3600), glob("{$path}/*"));

    $this->render = function (Application $app, string $theme = 'sage') {
        Container::setInstance($app);

        $expected = [
            'greeting' => '<p>Hello Acorn</p>',
            'partials.item' => '<li>Acorn</li>',
            'theme' => "<p>{$theme}</p>",
        ];

        foreach ($expected as $name => $html) {
            $view = $app['view']->make($name, ['name' => 'Acorn', 'item' => 'Acorn']);

            expect($view->render())->toContain($html)->and($view->makeLoader())->toBeFile();
        }
    };
});

it('creates a loader for each view', function () {
    $compiled = ($this->compiledDir)();

    $app = ($this->makeApp)(($this->theme)(temp('view_cache/' . uniqid())), $compiled);

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
    bool $moveInstall,
) {
    $buildBase = ($this->theme)(temp('view_cache/' . uniqid()));
    $buildCompiled = ($this->compiledDir)();

    $config = ['relative_hash' => $relativeHash];

    expect(($this->cache)(($this->makeApp)($buildBase, $buildCompiled, $config)))->toBe(0);

    $base = $moveInstall ? ($this->theme)(temp('view_cache/' . uniqid())) : $buildBase;
    $compiled = ($this->compiledDir)();
    (new Filesystem())->copyDirectory($buildCompiled, $compiled);

    ($this->backdate)($compiled);

    $cached = ($this->compiledFiles)($compiled);

    ($this->render)(($this->makeApp)($base, $compiled, [...$config, 'check_cache_timestamps' => false]));

    expect(($this->compiledFiles)($compiled))->toBe($cached);
})->with([
    'compiled path' => [false, false],
    'install with relative hashes' => [true, true],
]);

it('clears stale compiled views on single sites', function () {
    $compiled = ($this->compiledDir)();
    touch("{$compiled}/stale.php");

    expect(($this->cache)(($this->makeApp)(($this->theme)(temp('view_cache/' . uniqid())), $compiled)))
        ->toBe(0)
        ->and("{$compiled}/stale.php")
        ->not->toBeFile();
});

it('keeps themes apart in a shared compiled path on multisite with relative hashes', function () {
    $this->multisite = true;

    $root = temp('view_cache/' . uniqid());
    $compiled = ($this->compiledDir)();

    $config = ['relative_hash' => true, 'check_cache_timestamps' => false];

    $first = ($this->makeApp)(($this->theme)($root, 'first'), $compiled, $config);
    $second = ($this->makeApp)(($this->theme)($root, 'second'), $compiled, $config);

    expect(($this->cache)($first))->toBe(0);

    $cached = ($this->compiledFiles)($compiled);

    expect(($this->cache)($second))
        ->toBe(0)
        ->and(($this->compiledFiles)($compiled))
        ->toHaveCount(count($cached) * 2);

    ($this->backdate)($compiled);

    $cached = ($this->compiledFiles)($compiled);

    ($this->render)($first, 'first');
    ($this->render)($second, 'second');

    expect(($this->compiledFiles)($compiled))->toBe($cached);
});
