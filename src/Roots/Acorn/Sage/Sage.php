<?php

namespace Roots\Acorn\Sage;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\View\FileViewFinder;

class Sage
{
    use Concerns\FiltersBodyClass;
    use Concerns\FiltersEnqueues;
    use Concerns\FiltersTemplates;
    use Concerns\FiltersThePost;
    use Concerns\FiltersViews;

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
     * The Filesystem instance.
     */
    protected Filesystem $files;

    /**
     * Creates a new Sage instance.
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
        return method_exists($this, $filter) ?
            [$this, $filter] :
            [$this, 'filter'.Str::studly($filter)];
    }
}
