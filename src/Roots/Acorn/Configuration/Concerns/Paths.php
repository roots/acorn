<?php

namespace Roots\Acorn\Configuration\Concerns;

use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;

trait Paths
{
    /**
     * Infer the application's base directory from the environment.
     *
     * @return string
     */
    public static function inferBasePath()
    {
        return match (true) {
            isset($_ENV['APP_BASE_PATH']) => $_ENV['APP_BASE_PATH'],

            defined('ACORN_BASEPATH') => constant('ACORN_BASEPATH'),

            is_file($composerPath = get_theme_file_path('composer.json')) => dirname($composerPath),

            is_dir($appPath = get_theme_file_path('app')) => dirname($appPath),

            optional($vendorPath = (new Filesystem)->closest(dirname(__DIR__, 6), 'composer.json'), 'is_file') => dirname($vendorPath),

            default => dirname(__DIR__, 5),
        };
    }

    /**
     * Register and configure the application's paths.
     *
     * @return $this
     */
    public function withPaths(?string $app = null, ?string $config = null, ?string $storage = null, ?string $resources = null, ?string $public = null, ?string $bootstrap = null, ?string $lang = null, ?string $database = null)
    {
        $this->app->usePaths(
            array_filter(compact('app', 'config', 'storage', 'resources', 'public', 'bootstrap', 'lang', 'database')) + $this->defaultPaths()
        );

        return $this;
    }

    /**
     * Use the configured default paths.
     */
    public function defaultPaths(): array
    {
        $paths = [];

        foreach (['app', 'config', 'storage', 'resources', 'public', 'lang', 'database'] as $path) {
            $paths[$path] = $this->normalizeApplicationPath($path);
        }

        $paths['bootstrap'] = $this->normalizeApplicationPath($path, "{$paths['storage']}/framework");

        return $paths;
    }

    /**
     * Normalize a relative or absolute path to an application directory.
     */
    protected function normalizeApplicationPath(string $path, ?string $default = null): string
    {
        $key = strtoupper($path);

        if (is_null($env = Env::get("ACORN_{$key}_PATH"))) {
            return $default
                ?? (defined("ACORN_{$key}_PATH") ? constant("ACORN_{$key}_PATH") : $this->findPath($path));
        }

        return Str::startsWith($env, ['/', '\\'])
            ? $env
            : $this->app->basePath($env);
    }

    /**
     * Find a path that is configurable by the developer.
     */
    protected function findPath(string $path): string
    {
        $path = trim($path, '\\/');

        $method = $path === 'app' ? 'path' : "{$path}Path";

        $searchPaths = [
            method_exists($this->app, $method) ? $this->app->{$method}() : null,
            $this->app->basePath($path),
            get_theme_file_path($path),
        ];

        return collect($searchPaths)
            ->filter(fn ($path) => (is_string($path) && is_dir($path)))
            ->whenEmpty(fn ($paths) => $paths->add($this->fallbackPath($path)))
            ->unique()
            ->first();
    }

    /**
     * Fallbacks for path types.
     */
    protected function fallbackPath(string $path): string
    {
        return $path === 'storage'
            ? $this->fallbackStoragePath()
            : $this->app->basePath($path);
    }

    /**
     * Ensure that all of the storage directories exist.
     */
    protected function fallbackStoragePath(): string
    {
        $files = new Filesystem;
        $path = Str::finish(WP_CONTENT_DIR, '/cache/acorn');

        foreach ([
            'framework/cache/data',
            'framework/views',
            'framework/sessions',
            'logs',
        ] as $directory) {
            $files->ensureDirectoryExists("{$path}/{$directory}", 0755, true);
        }

        return $path;
    }
}
