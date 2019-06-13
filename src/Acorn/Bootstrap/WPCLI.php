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
            $this->app->make(\Roots\Acorn\Console\Commands\ViewCacheCommand::class)
        );

        \WP_CLI::add_command(
            'acorn view:clear',
            $this->app->make(\Roots\Acorn\Console\Commands\ViewClearCommand::class)
        );

        \WP_CLI::add_command(
            'acorn config:cache',
            $this->app->make(\Roots\Acorn\Console\Commands\ConfigCacheCommand::class)
        );

        \WP_CLI::add_command(
            'acorn config:clear',
            $this->app->make(\Roots\Acorn\Console\Commands\ConfigClearCommand::class)
        );

        \WP_CLI::add_command(
            'acorn vendor:publish',
            $this->app->make(\Roots\Acorn\Console\Commands\VendorPublishCommand::class)
        );

        \WP_CLI::add_command(
            'acorn make:provider',
            $this->app->make(\Roots\Acorn\Console\Commands\ProviderMakeCommand::class)
        );

        \WP_CLI::add_command(
            'acorn make:composer',
            $this->app->make(\Roots\Acorn\Console\Commands\ComposerMakeCommand::class)
        );

        \WP_CLI::add_command(
            'acorn package:discover',
            $this->app->make(\Roots\Acorn\Console\Commands\PackageDiscoverCommand::class)
        );

        \WP_CLI::add_command(
            'acorn package:clear',
            $this->app->make(\Roots\Acorn\Console\Commands\PackageClearCommand::class)
        );

        \WP_CLI::add_command(
            'acorn optimize',
            $this->app->make(\Roots\Acorn\Console\Commands\OptimizeCommand::class)
        );

        \WP_CLI::add_command(
            'acorn optimize:clear',
            $this->app->make(\Roots\Acorn\Console\Commands\OptimizeClearCommand::class)
        );
    }
}
