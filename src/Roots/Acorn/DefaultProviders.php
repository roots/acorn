<?php

namespace Roots\Acorn;

use Illuminate\Support\Collection;
use Illuminate\Support\DefaultProviders as DefaultProvidersBase;
use Illuminate\Support\Str;

class DefaultProviders extends DefaultProvidersBase
{
    /**
     * The Acorn providers.
     */
    protected array $acornProviders = [
        \Roots\Acorn\Assets\AssetsServiceProvider::class,
        \Roots\Acorn\Filesystem\FilesystemServiceProvider::class,
        \Roots\Acorn\Providers\AcornServiceProvider::class,
        \Roots\Acorn\Providers\QueueServiceProvider::class,
        \Roots\Acorn\View\ViewServiceProvider::class,
    ];

    /**
     * The additional framework providers.
     */
    protected array $additionalProviders = [
        \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        \Illuminate\Foundation\Providers\ComposerServiceProvider::class,
        \Illuminate\Database\MigrationServiceProvider::class,
    ];

    /**
     * The disallowed providers.
     */
    protected array $disallowedProviders = [
        \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        \Illuminate\View\ViewServiceProvider::class,
    ];

    /**
     * Create a new default provider collection.
     *
     * @return void
     */
    public function __construct(?array $providers = null)
    {
        parent::__construct($providers);

        $this->providers = array_unique($this->providers);

        if ($providers) {
            return;
        }

        $this->providers = Collection::make($this->providers)
            ->merge($this->acornProviders)
            ->filter(fn ($provider) => ! Str::contains($provider, $this->disallowedProviders))
            ->merge($this->additionalProviders)
            ->unique()
            ->all();
    }
}
