<?php

namespace Roots\Acorn\Bootstrap;

use Roots\Acorn\Application;

class WPCLI
{
    protected $app;

    public function bootstrap(Application $app)
    {
        $this->app = $app;

        if ($this->app->runningInConsole() && \defined('WP_CLI') && WP_CLI) {
            $this->registerCommands();
        }
    }

    protected function registerCommands()
    {
        \WP_CLI::add_command(
            'acorn view:cache',
            $this->app->make(\Roots\Acorn\Console\ViewCacheCommand::class)
        );

        \WP_CLI::add_command(
            'acorn view:clear',
            $this->app->make(\Roots\Acorn\Console\ViewClearCommand::class)
        );

        \WP_CLI::add_command(
            'acorn config:cache',
            $this->app->make(\Roots\Acorn\Console\ConfigCacheCommand::class)
        );

        \WP_CLI::add_command(
            'acorn config:clear',
            $this->app->make(\Roots\Acorn\Console\ConfigClearCommand::class)
        );

        \WP_CLI::add_command(
            'acorn vendor:publish',
            $this->app->make(\Roots\Acorn\Console\VendorPublishCommand::class)
        );

        \WP_CLI::add_command(
            'acorn make:provider',
            $this->app->make(\Roots\Acorn\Console\ProviderMakeCommand::class)
        );

        \WP_CLI::add_command(
            'acorn make:composer',
            $this->app->make(\Roots\Acorn\Console\ComposerMakeCommand::class)
        );

        \WP_CLI::add_command(
            'acorn package:discover',
            $this->app->make(\Roots\Acorn\Console\PackageDiscoverCommand::class)
        );
    }
}
