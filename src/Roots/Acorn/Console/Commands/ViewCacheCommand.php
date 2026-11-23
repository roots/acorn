<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\ViewCacheCommand as FoundationViewCacheCommand;
use Illuminate\Support\Collection;
use Roots\Acorn\View\FileViewFinder;
use Symfony\Component\Finder\SplFileInfo;

class ViewCacheCommand extends FoundationViewCacheCommand
{
    /**
     * Compile the given view files and create loaders for views that aren't namespaced.
     *
     * @return void
     */
    protected function compileViews(Collection $views)
    {
        parent::compileViews($views);

        $factory = $this->laravel['view'];
        $finder = $factory->getFinder();

        if (! $finder instanceof FileViewFinder) {
            return;
        }

        $views
            ->map(fn (SplFileInfo $file) => $finder->getPossibleViewNameFromPath($file->getPathname()))
            ->reject(fn ($view) => str_contains($view, '::'))
            ->unique()
            ->filter(fn ($view) => $factory->exists($view))
            ->each(fn ($view) => $factory->make($view)->makeLoader());
    }
}
