<?php

namespace Roots\Acorn;

use Illuminate\Foundation\ComposerScripts as FoundationComposerScripts;

class ComposerScripts extends FoundationComposerScripts
{
    /**
     * Clear the cached Laravel bootstrapping files.
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $laravel = new \Roots\Acorn\Application(getcwd(), ['bootstrap' => getcwd() .'/storage/framework']);
        
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
