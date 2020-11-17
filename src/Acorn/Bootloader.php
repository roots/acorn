<?php

namespace Roots\Acorn;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Roots\Acorn\Application;

use function Roots\add_filters;
use function Roots\env;

class Bootloader
{
    /** @var string Application to be instantiated at boot time */
    protected $application_class;

    /** @var string[] WordPress hooks that will boot application */
    protected $boot_hooks;

    /** @var callable[] Callbacks to be run when application boots */
    protected $queue = [];

    /** @var bool Signals that application is ready to boot */
    protected $ready = false;

    /**
     * Create a new bootloader instance
     *
     * @param string|iterable $boot_hooks WordPress hooks to boot application
     * @param string $application_class Application class
     */
    public function __construct(
        $boot_hooks = ['after_setup_theme', 'rest_api_init'],
        string $application_class = Application::class
    ) {
        $this->application_class = $application_class;
        $this->boot_hooks = (array) $boot_hooks;

        add_filters($this->boot_hooks, $this, 5);
    }

    /**
     * Enqueues callback to be loaded with application
     *
     * @param callable $callback
     * @return static;
     */
    public function call(callable $callback): Bootloader
    {
        if (! $this->ready()) {
            $this->queue[] = $callback;
            return $this;
        }

        $this->app()->call($callback);
        return $this;
    }

    /**
     * Determines whether the application is ready to boot
     *
     * @return bool
     */
    public function ready(): bool
    {
        if ($this->ready) {
            return true;
        }

        foreach ($this->boot_hooks as $hook) {
            if (\did_action($hook) || \doing_action($hook)) {
                return $this->ready = true;
            }
        }

        return $this->ready = !! \apply_filters('acorn/ready', false);
    }

    /**
     * Boot the Application
     */
    public function __invoke()
    {
        static $app;

        if (! $this->ready()) {
            return;
        }

        $app = $this->app();

        foreach ($this->queue as $callback) {
            $app->call($callback);
        }
        $this->queue = [];

        $app->boot();
    }

    /**
     * Get application instance
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function app(): ApplicationContract
    {
        static $app;

        if ($app) {
            return $app;
        }

        $bootstrap = $this->bootstrap();
        $basepath = $this->basePath();

        $app = new $this->application_class($basepath);
        $app->bootstrapWith($bootstrap);

        return $app;
    }

    /**
     * Get the application basepath
     *
     * @return string
     */
    protected function basePath(): string
    {
        $basepath = \dirname(\locate_template('config') ?: __DIR__ . '/../');

        $basepath = \defined('ACORN_BASEPATH') ? \ACORN_BASEPATH : env('ACORN_BASEPATH', $basepath);

        return \apply_filters('acorn/basepath', $basepath);
    }

    /**
     * Get the list of application bootstraps
     *
     * @return string[]
     */
    protected function bootstrap(): array
    {
        $bootstrap = [
            \Roots\Acorn\Bootstrap\SageFeatures::class,
            \Roots\Acorn\Bootstrap\LoadConfiguration::class,
            \Roots\Acorn\Bootstrap\HandleExceptions::class,
            \Roots\Acorn\Bootstrap\RegisterGlobals::class,
            \Roots\Acorn\Bootstrap\LoadBindings::class,
            \Roots\Acorn\Bootstrap\RegisterProviders::class,
            \Roots\Acorn\Bootstrap\Console::class,
        ];

        return \apply_filters('acorn/bootstrap', $bootstrap);
    }
}
