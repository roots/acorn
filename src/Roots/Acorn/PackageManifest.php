<?php

namespace Roots\Acorn;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest as FoundationPackageManifest;

class PackageManifest extends FoundationPackageManifest
{
    /**
     * The composer.json paths.
     *
     * @var string[]
     */
    public $composerPaths;

    /**
     * Create a new package manifest instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string[]  $composerPaths
     * @param  string  $manifestPath
     * @return void
     */
    public function __construct(Filesystem $files, array $composerPaths, $manifestPath)
    {
        $this->files = $files;
        $this->composerPaths = $composerPaths;
        $this->manifestPath = $manifestPath;
    }

    /**
     * Get a package name based on its provider
     *
     * @param string $provider_name
     * @return string
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function getPackage($provider_name)
    {
        foreach ($this->getManifest() as $package => $configuration) {
            foreach ($configuration['providers'] ?? [] as $provider) {
                if ($provider !== $provider_name) {
                    continue;
                }

                return $package;
            }
        }

        return '';
    }

    /**
     * Build the manifest and write it to disk.
     *
     * @return void
     */
    public function build()
    {
        $packages = array_reduce($this->composerPaths, function ($all, $composerPath) {
            $packages = [];

            $path = "${composerPath}/vendor/composer/installed.json";

            if ($this->files->exists($path)) {
                $installed = json_decode($this->files->get($path), true);

                $packages = $installed['packages'] ?? $installed;
            }

            $packages[] = json_decode($this->files->get("${composerPath}/composer.json"), true);

            $ignoreAll = in_array('*', $ignore = $this->packagesToIgnore());

            return collect($packages)->mapWithKeys(function ($package) use ($path, $composerPath) {
                return [
                    $this->format($package['name'] ?? basename($composerPath), dirname($path, 2)) =>
                        $package['extra']['acorn'] ?? $package['extra']['laravel'] ?? []
                ];
            })->each(function ($configuration) use (&$ignore) {
                $ignore = array_merge($ignore, $configuration['dont-discover'] ?? []);
            })->reject(function ($configuration, $package) use ($ignore, $ignoreAll) {
                return $ignoreAll || in_array($package, $ignore);
            })->filter()->merge($all)->all();
        }, []);

        $this->write($packages);
    }

    /**
     * Format the given package name.
     *
     * @param  string  $package
     * @param  string  $vendorPath
     * @return string
     */
    protected function format($package, $vendorPath = null)
    {
        return str_replace($vendorPath . '/', '', $package);
    }

    /**
     * Get all of the package names that should be ignored.
     *
     * @return array
     */
    protected function packagesToIgnore()
    {
        return array_reduce($this->composerPaths, function ($ignore, $composerPath) {
            $path = "${composerPath}/composer.json";

            if (! $this->files->exists($path)) {
                return $ignore;
            }

            $package = json_decode($this->files->get($path), true);

            return array_merge(
                $ignore,
                $package['extra']['laravel']['dont-discover'] ?? [],
                $package['extra']['acorn']['dont-discover'] ?? []
            );
        }, []);
    }
}
