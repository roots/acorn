<?php

namespace Roots\Acorn;

use Illuminate\Foundation\ComposerScripts as FoundationComposerScripts;
use Roots\Acorn\Console\Console;
use Roots\Acorn\Filesystem\Filesystem;

class ComposerScripts extends FoundationComposerScripts
{
    /**
     * Clear the cached Laravel bootstrapping files.
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $console = new Console(new Filesystem(), getcwd());

        if (version_compare(PHP_VERSION, '8.1.0', '>=') && version_compare(PHP_VERSION, '8.1.6', '<')) {
            printf(
                "\033[93m⚠ PHP %s has a known bug that can impact certain environments. You should consider updating to a more recent version\n%s\n%s.\033[0m\n",
                PHP_VERSION,
                '🔗 https://github.com/roots/acorn/issues/217',
                '🔗 https://github.com/php/php-src/pull/8297'
            );
        }

        $console->configClear();
        $console->clearCompiled();
    }
}
