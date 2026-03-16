<?php

namespace Roots\Acorn;

use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Foundation\Providers\ComposerServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\DefaultProviders as DefaultProvidersBase;
use Illuminate\Support\Str;
use Illuminate\View\ViewServiceProvider;
use Roots\Acorn\Assets\AssetsServiceProvider;
use Roots\Acorn\Filesystem\FilesystemServiceProvider;
use Roots\Acorn\Providers\AcornServiceProvider;
use Roots\Acorn\Providers\QueueServiceProvider;

class DefaultProviders extends DefaultProvidersBase
{
    /**
     * The Acorn providers.
     */
    protected array $acornProviders = [
        AssetsServiceProvider::class,
        FilesystemServiceProvider::class,
        AcornServiceProvider::class,
        QueueServiceProvider::class,
        View\ViewServiceProvider::class,
    ];

    /**
     * The additional framework providers.
     */
    protected array $additionalProviders = [
        FoundationServiceProvider::class,
        ComposerServiceProvider::class,
        MigrationServiceProvider::class,
    ];

    /**
     * The disallowed providers.
     */
    protected array $disallowedProviders = [
        ConsoleSupportServiceProvider::class,
        ViewServiceProvider::class,
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
