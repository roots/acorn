<?php

namespace Roots\Acorn\Sage;

use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\View\FileViewFinder;

class ViewFinder
{
    /**
     * The FileViewFinder instance.
     *
     * @var \Roots\Acorn\View\FileViewFinder
     */
    protected $finder;

    /**
     * The Filesystem instance.
     *
     * @var \Roots\Acorn\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Base path for theme or plugin in which views are located.
     *
     * @var string
     */
    protected $path;

    public function __construct(FileViewFinder $finder, Filesystem $files, $path = STYLESHEETPATH)
    {
        $this->finder = $finder;
        $this->files = $files;
        $this->path = realpath($path) ?: $path;
    }

    /**
     * Locate available view files.
     *
     * @param  mixed  $file
     * @return array
     */
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
     * Return the FileViewFinder instance.
     *
     * @return \Roots\Acorn\View\FileViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Return the Filesystem instance.
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
     * @return array
     */
    protected function getRelativeViewPaths()
    {
        return collect($this->finder->getPaths())
        ->map(function ($viewsPath) {
            return $this->files->getRelativePath("{$this->path}/", $viewsPath);
        });
    }
}
