<?php

namespace Roots\Acorn\View;

use Illuminate\View\FileViewFinder as FileViewFinderBase;

class FileViewFinder extends FileViewFinderBase
{
    /**
     * Get possible relative locations of view files
     *
     * @param  string   $path Absolute or relative path to possible view file
     * @return string[]
     */
    public function getPossibleViewFilesFromPath($path)
    {
        $path = $this->getPossibleViewNameFromPath($path);
        return $this->getPossibleViewFiles($path);
    }

    /**
     * Get possible view name based on path
     *
     * @param  string $path Absolute or relative path to possible view file
     * @return string
     */
    public function getPossibleViewNameFromPath($file)
    {
        $namespace = null;
        $view = $this->normalizePath($file);
        $paths = $this->normalizePath($this->paths);
        $hints = array_map([$this, 'normalizePath'], $this->hints);

        $view = $this->stripExtensions($view);
        $view = str_replace($paths, '', $view);

        foreach ($hints as $hintNamespace => $hintPaths) {
            $test = str_replace($hintPaths, '', $view);
            if ($view !== $test) {
                $namespace = $hintNamespace;
                $view = $test;
                break;
            }
        }

        $view = ltrim($view, '/\\');

        if ($namespace) {
            $view = "{$namespace}::$view";
        }

        return $view;
    }

    /**
     * Remove recognized extensions from path
     *
     * @param  string $file relative path to view file
     * @return string view name
     */
    protected function stripExtensions($path)
    {
        $extensions = implode('|', array_map('preg_quote', $this->getExtensions()));

        return preg_replace("/\.({$extensions})$/", '', $path);
    }

    /**
     * Normalize paths
     *
     * @param  string|string[] $path
     * @param  string          $separator
     * @return string|string[]
     */
    protected function normalizePath($path, $separator = '/')
    {
        return preg_replace('#[\\/]+#', $separator, $path);
    }
}
