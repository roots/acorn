<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\ViewCacheCommand as FoundationViewCacheCommand;
use Illuminate\Support\Collection;
use Roots\Acorn\View\FileViewFinder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class ViewCacheCommand extends FoundationViewCacheCommand
{
    /**
     * Execute the console command.
     *
     * Compiled views aren't cleared first on multisite, where sites share a
     * compiled path by default, so caching one site keeps the others' views.
     *
     * @return void
     */
    public function handle()
    {
        if (! function_exists('is_multisite') || ! is_multisite()) {
            $this->callSilent('view:clear');
        }

        $this->paths()->each(function ($path) {
            $prefix = $this->output->isVeryVerbose() ? '<fg=yellow;options=bold>DIR</> ' : '';

            $this->components->task($prefix . $path, null, OutputInterface::VERBOSITY_VERBOSE);

            $this->compileViews($this->bladeFilesIn([$path]));
        });

        $this->newLine();

        $this->components->info('Blade templates cached successfully.');
    }

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
