<?php

namespace Roots\Acorn;

use Illuminate\Foundation\ComposerScripts as FoundationComposerScripts;
use Roots\Acorn\Console\Concerns\GetsFreshApplication;

class ComposerScripts extends FoundationComposerScripts
{
    use GetsFreshApplication;

    /**
     * Clear the cached Laravel bootstrapping files.
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $laravel = (new self)->getFreshApplication();

        if (is_file($configPath = $laravel->getCachedConfigPath())) {
            @unlink($configPath);
        }

        if (is_file($servicesPath = $laravel->getCachedServicesPath())) {
            @unlink($servicesPath);
        }

        if (is_file($packagesPath = $laravel->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }
    }
}
