<?php

namespace Roots\Sage;

use Roots\Acorn\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;

class ViewFinder
{
    /** @var \Roots\Acorn\View\FileViewFinder */
    protected $finder;

    /** @var string Path used by WordPress's internal `locate_template()` function */
    protected $path;

    public function __construct(FileViewFinder $finder, $path = STYLESHEETPATH)
    {
        $this->finder = $finder;
        $this->path = $path;
    }

    public function locate($view)
    {
        if (is_iterable($view)) {
            return array_merge(...array_map([$this, 'locate'], $view));
        }

        $views = array_unique(array_merge(...array_map(function ($view) {
            return array_unique(array_merge([$view], array_map(function ($viewPath) use ($view) {
                return "{$viewPath}/{$view}";
            }, $this->getRelativeViewPaths())));
        }, $this->finder->getPossibleViewFilesFromPath($view))));

        $views = array_map('trim', $views, array_fill(0, count($views), '\\/.'));

        return $views;
    }

    /**
     * Set the absolute base path
     *
     * @param string $path Typically the path used by WordPress's internal `locate_template()` function
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the current absolute base path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
     * Get relative view paths from absolute base path
     *
     * @return array relative view paths
     */
    protected function getRelativeViewPaths()
    {
        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $this->finder->getFilesystem();

        return array_unique(array_map(function ($viewsPath) use ($filesystem) {
            return $filesystem->getRelativePath("{$this->path}/", $viewsPath);
        }, $this->finder->getPaths()));
    }
}
