<?php

namespace Roots\Acorn\Sage;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\View\FileViewFinder;

class Sage
{
    use Concerns\FiltersBodyClass;
    use Concerns\FiltersTemplates;
    use Concerns\FiltersThePost;
    use Concerns\FiltersViews;
    use Concerns\FiltersEnqueues;

    /**
     * The application implementation.
     *
     * @var ContainerContract
     */
    protected $app;

    /**
     * The ViewFinder instance.
     *
     * @var ViewFinder
     */
    protected $sageFinder;

    /**
     * The FileViewFinder instance.
     *
     * @var FileViewFinder
     */
    protected $fileFinder;

    /**
     * The View Factory instance.
     *
     * @var ViewFactory
     */
    protected $view;

    /**
     * Creates a new Sage instance.
     *
     * @param Filesystem        $files
     * @param ViewFinder        $sageFinder
     * @param FileViewFinder    $fileFinder
     * @param ViewFactory       $view
     * @param ContainerContract $app
     */
    public function __construct(
        Filesystem $files,
        ViewFinder $sageFinder,
        FileViewFinder $fileFinder,
        ViewFactory $view,
        ContainerContract $app
    ) {
        $this->app = $app;
        $this->files = $files;
        $this->fileFinder = $fileFinder;
        $this->sageFinder = $sageFinder;
        $this->view = $view;
    }

    /**
     * Get filter to be passed to WordPress
     *
     * @return array
     */
    public function filter($filter)
    {
        if (method_exists($this, $filter)) {
            return [$this, $filter];
        }
        return [$this, 'filter' . Str::studly($filter)];
    }
}
