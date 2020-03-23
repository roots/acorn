<?php

namespace Roots\Acorn\View;

use ReflectionClass;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\View\ViewServiceProvider as ViewServiceProviderBase;
use Roots\Acorn\View\Composers\Debugger;
use Roots\Acorn\View\Directives\InjectionDirective;
use Symfony\Component\Finder\Finder;

class ViewServiceProvider extends ViewServiceProviderBase
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->registerMacros();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->attachDirectives();
        $this->attachComponents();
        $this->attachComposers();

        if ($this->app['config']['view.debug']) {
            $this->attachDebugger();
        }
    }

    /**
     * Return an instance of View.
     *
     * @return \Illuminate\View\View
     */
    protected function view()
    {
        return $this->app['view'];
    }

    /**
     * Register View Finder
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            $finder = new FileViewFinder($app['files'], array_unique($app['config']['view.paths']));

            foreach ($app['config']['view.namespaces'] as $namespace => $hints) {
                $hints = array_merge(array_map(function ($path) use ($namespace) {
                    return "{$path}/vendor/{$namespace}";
                }, $finder->getPaths()), (array) $hints);

                $finder->addNamespace($namespace, $hints);
            }

            return $finder;
        });

        $this->app->alias('view.finder', FileViewFinder::class);
    }

    /**
     * Register View Macros
     *
     * @return void
     */
    public function registerMacros()
    {
        /**
         * Get the compiled path of the view
         *
         * @return string
         */
        View::macro('getCompiled', function () {
            /** @var string $file path to file */
            $file = $this->getPath();

            /** @var \Illuminate\Contracts\View\Engine $engine */
            $engine = $this->getEngine();

            return ($engine instanceof \Illuminate\View\Engines\CompilerEngine)
                ? $engine->getCompiler()->getCompiledPath($file)
                : $file;
        });

        /**
         * Creates a loader for the view to be called later
         *
         * @return string
         */
        View::macro('makeLoader', function () {
            $view = $this->getName();
            $compiled = $this->getCompiled();
            $id = basename($compiled, '.php');
            $loader = dirname($compiled) . "/{$id}-loader.php";

            if (! file_exists($loader)) {
                file_put_contents($loader, "<?= \\Roots\\view('{$view}', \$data ?? get_defined_vars())->render(); ?>");
            }

            return $loader;
        });
    }

    /**
     * Preflight
     *
     * @param  Filesystem $files
     * @return void
     */
    public function preflight(Filesystem $files)
    {
        $storageDir = $this->app['config']['view.compiled'];

        if (! $files->exists($storageDir)) {
            $files->makeDirectory($storageDir, 0755, true);
        }
    }

    /**
     * Attach View Directives
     *
     * @return void
     */
    public function attachDirectives()
    {
        $blade = $this->view()->getEngineResolver()->resolve('blade')->getCompiler();
        $directives = $this->app['config']['view.directives'];
        $directives += ['inject' => InjectionDirective::class];

        foreach ($directives as $name => $handler) {
            if (! is_callable($handler)) {
                $handler = $this->app->make($handler);
            }

            $blade->directive($name, $handler);
        }
    }

    /**
     * Attach View Components
     *
     * @return void
     */
    public function attachComponents()
    {
        $components = $this->app->config['view.components'];

        if (is_array($components) && Arr::isAssoc($components)) {
            $blade = $this->view()->getEngineResolver()->resolve('blade')->getCompiler();

            foreach ($components as $alias => $view) {
                $blade->component($view, $alias);
            }
        }
    }

    /**
     * Attach View Composers
     *
     * @return void
     */
    public function attachComposers()
    {
        if (! is_dir($path = $this->app->path('View/Composers'))) {
            return;
        }

        $namespace = $this->app->getNamespace();

        // TODO: This should be cacheable, perhaps via `wp acorn` command
        foreach ((new Finder())->in($path)->files() as $composer) {
            $composer = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($composer->getPathname(), $this->app->path() . DIRECTORY_SEPARATOR)
            );

            if (
                is_subclass_of($composer, Composer::class) &&
                ! (new ReflectionClass($composer))->isAbstract()
            ) {
                $this->view()->composer($composer::views(), $composer);
            }
        }
    }

    /**
     * Attach View Debugger
     *
     * @return void
     */
    public function attachDebugger()
    {
        $this->view()->composer('*', Debugger::class);
    }
}
