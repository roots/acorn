<?php

namespace Roots\Acorn;

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
     * Build the manifest and write it to disk.
     *
     * @return void
     */
    public function build()
    {
        $packages = [];

        foreach ($this->composerPaths as $path) {
            if ($this->files->exists($path)) {
                $installed = json_decode($this->files->get($path), true);

                $packages = array_merge($packages, $installed['packages'] ?? $installed);
            }
        }

        $ignoreAll = in_array('*', $ignore = $this->packagesToIgnore());

        $this->write(collect($packages)->mapWithKeys(function ($package) {
            return [$this->format($package['name']) => $package['extra']['acorn'] ?? []];
        })->each(function ($configuration) use (&$ignore) {
            $ignore = array_merge($ignore, $configuration['dont-discover'] ?? []);
        })->reject(function ($configuration, $package) use ($ignore, $ignoreAll) {
            return $ignoreAll || in_array($package, $ignore);
        })->filter()->all());
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
        return str_replace($this->vendorPath . '/', '', $package);
    }

    /**
     * Get all of the package names that should be ignored.
     *
     * @return array
     */
    protected function packagesToIgnore()
    {
        $ignore = [];

        foreach ($this->composerPaths as $path) {
            if (! $this->files->exists($path)) {
                continue;
            }

            $ignore = array_merge($ignore, json_decode(
                $this->files->get($path),
                true
            )['extra']['laravel']['dont-discover'] ?? []);
        }

        return $ignore;
    }
}
