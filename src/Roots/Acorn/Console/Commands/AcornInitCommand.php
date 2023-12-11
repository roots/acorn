<?php

namespace Roots\Acorn\Console\Commands;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Filesystem\Filesystem;

class AcornInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
    acorn:init
    {path?* : Application path to initialize in the base directory}
    {--base= : Application base directory}
    SIGNATURE;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes required paths in the base directory.';

    /**
     * Available application paths.
     *
     * @var string[]
     */
    protected $paths = [
        'app' => 'app',
        'bootstrap' => 'storage/framework',
        'config' => 'config',
        'database' => 'database',
        'lang' => 'resources/lang',
        'public' => 'public',
        'resources' => 'resources',
        'storage' => 'storage',
    ];

    /**
     * Default application paths to be initialized.
     *
     * @var string[]
     */
    protected $defaults = ['config'];

    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Path to Application base directory.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files, Application $app)
    {
        parent::__construct();

        $this->app = $app;
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->basePath = realpath($this->option('base') ?: $this->app->basePath());

        if (! is_writable($this->basePath)) {
            throw new Exception("The {$this->basePath} directory must be present and writable.");
        }

        if ($this->basePath === dirname(__DIR__, 5)) {
            throw new Exception("The {$this->basePath} directory is invalid. Specify an alternative using <comment>--base</comment> option.");
        }

        $paths = array_map('strtolower', array_intersect(
            $this->argument('path') ?: $this->defaultPaths(),
            array_keys($this->paths)
        ));

        foreach ($paths as $key) {
            if ($this->initPath($key, $path = $this->paths[$key])) {
                $this->line("<info>Initialized</info> <comment>[{$this->basePath}/{$path}]</comment>");
            }
        }
    }

    /**
     * Get default paths to be initialized.
     *
     * @return string[]
     */
    protected function defaultPaths()
    {
        return $this->defaults;
    }

    /**
     * Initialize the given path.
     *
     * @param  string  $key
     * @param  string  $path
     * @return bool
     */
    protected function initPath($key, $path)
    {
        if (! $this->createPath($path)) {
            return false;
        }

        if (method_exists($this->app, 'usePaths')) {
            $this->app->usePaths([$key => $path]);
        }

        if (method_exists($this->app, $method = 'use'.ucfirst($key))) {
            $this->app->{$method}($path);
        }

        return true;
    }

    /**
     * Initialize the given path.
     *
     * @param  string  $path
     * @return bool
     */
    protected function createPath($path)
    {
        $this->files->ensureDirectoryExists("{$this->basePath}/{$path}", 0755, true);

        if ($this->files->isDirectory($from = __DIR__."/stubs/paths/{$path}")) {
            return $this->files->copyDirectory($from, "{$this->basePath}/{$path}");
        }

        return $this->files->isDirectory("{$this->basePath}/{$path}");
    }
}
