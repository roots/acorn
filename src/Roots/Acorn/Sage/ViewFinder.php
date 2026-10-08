<?php

namespace Roots\Acorn\Sage;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\View\FileViewFinder;

class ViewFinder
{
    /**
     * The FileViewFinder instance.
     *
     * @var FileViewFinder
     */
    protected $finder;

    /**
     * The Filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Base path for theme or plugin in which views are located.
     *
     * @var string
     */
    protected $path;

    /**
     * Create new ViewFinder instance.
     *
     * @param  string  $path
     * @return void
     */
    public function __construct(FileViewFinder $finder, Filesystem $files, $path = '')
    {
        $this->finder = $finder;
        $this->files = $files;
        $this->path = realpath($path ?: get_theme_file_path());
    }

    /**
     * Locate available view files.
     *
     * @param  mixed  $file
     * @return array
     */
    public function locate($file)
    {
        if (is_array($file)) {
            return array_merge(...array_map([$this, 'locate'], $file));
        }

        return $this
            ->getRelativeViewPaths()
            ->flatMap(fn ($viewPath) => collect($this->finder->getPossibleViewFilesFromPath($file))
                ->merge([$file])
                ->map(fn ($file) => "{$viewPath}/{$file}"))
            ->unique()
            ->map(fn ($file) => trim($file, '\\/'))
            ->toArray();
    }

    /**
     * Resolve the first Blade view in a template hierarchy, stopping at the
     * template WordPress already located.
     *
     * A candidate only counts as a Blade view when it resolves inside one of
     * the registered view paths, so `..` segments never escape them.
     *
     * @param  string[]  $templates
     * @param  string|false  $located
     * @return string|null
     */
    public function resolve($templates, $located = false)
    {
        $directories = array_unique([get_stylesheet_directory(), get_template_directory()]);
        $viewPaths = [];

        foreach ($this->finder->getPaths() as $path) {
            $viewPaths[] = trailingslashit(wp_normalize_path(realpath($path) ?: $path));
        }

        foreach ($templates as $name) {
            foreach ($directories as $directory) {
                $path = realpath("{$directory}/{$name}");

                if ($path === false) {
                    continue;
                }

                if ($path === $located) {
                    return null;
                }

                if (Str::startsWith(wp_normalize_path($path), $viewPaths)) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * Return the FileViewFinder instance.
     *
     * @return FileViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Return the Filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get list of view paths relative to the base path
     *
     * @return Collection
     */
    protected function getRelativeViewPaths()
    {
        return collect($this->finder->getPaths())->map(fn ($viewsPath) => $this->files->getRelativePath(
            "{$this->path}/",
            $viewsPath,
        ));
    }
}
