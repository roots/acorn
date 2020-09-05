<?php

namespace Roots\Acorn;

use Illuminate\Events\EventServiceProvider;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Log\LogServiceProvider;

/**
 * Application container
 */
class Application extends FoundationApplication
{
    /**
     * The Acorn framework version.
     *
     * @var string
     */
    public const VERSION = 'Acorn 2.x (Laravel ' . parent::VERSION .  ')';

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new LogServiceProvider($this));
        // $this->register(new RoutingServiceProvider($this));
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        parent::registerCoreContainerAliases();

        $aliases = [
            'app'             => self::class,
            'assets.manifest' => \Acorn\Assets\Manifest::class,
            'config'          => \Acorn\Config::class,
            'files'           => \Acorn\Filesystem\Filesystem::class,
            'view.finder'     => \Acorn\View\FileViewFinder::class
        ];

        foreach ($aliases as $key => $alias) {
            $this->alias($key, $alias);
        }
    }
}
