<?php

namespace Roots\Acorn\Sage;

use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\View\FileViewFinder;

class ViewFinder
{
    /** @var \Roots\Acorn\View\FileViewFinder */
    protected $finder;

    /** @var \Roots\Acorn\Filesystem\Filesystem */
    protected $files;

    /** @var string Base path for theme or plugin in which views are located */
    protected $base_path;

    public function __construct(FileViewFinder $finder, Filesystem $files, $base_path = STYLESHEETPATH)
    {
        $this->finder = $finder;
        $this->files = $files;
        $this->base_path = realpath($base_path) ?: $base_path;
    }

    public function locate($file)
    {
        if (is_iterable($file)) {
            return array_merge(...array_map([$this, 'locate'], $file));
        }

        return $this->getRelativeViewPaths()
            ->flatMap(function ($viewPath) use ($file) {
                return collect($this->finder->getPossibleViewFilesFromPath($file))
                    ->merge([$file])
                    ->map(function ($file) use ($viewPath) {
                        return "{$viewPath}/{$file}";
                    });
            })
            ->unique()
            ->map(function ($file) {
                return trim($file, '\\/');
            })
            ->toArray();
    }

    /**
     * Get view finder
     *
     * @return \Roots\Acorn\View\FileViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Get view finder
     *
     * @return \Roots\Acorn\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get list of view paths relative to the base path
     *
     * @return array relative view paths
     */
    protected function getRelativeViewPaths()
    {
        return collect($this->finder->getPaths())
        ->map(function ($viewsPath) {
            return $this->files->getRelativePath("{$this->base_path}/", $viewsPath);
        });
    }
}
