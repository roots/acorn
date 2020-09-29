<?php

namespace Roots\Acorn\Bootstrap;

use WP_CLI;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Console\AcornWpCliCommand;

class RegisterConsole
{
    /**
     * Bootstrap the given application.
     *
     * @param  Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (!$app->runningInConsole() || !class_exists('WP_CLI')) {
            return;
        }

        WP_CLI::add_command('acorn', new AcornWpCliCommand($app));
    }
}
