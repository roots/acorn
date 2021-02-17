<?php

namespace Roots\Acorn\Bootstrap;

use WP_CLI;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Roots\Acorn\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use TypeError;

class RegisterConsole
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        if (! $this->app->runningInConsole() || ! class_exists('WP_CLI')) {
            return;
        }

        WP_CLI::add_command('acorn', function () {
            $args = array_slice($_SERVER['argv'], 1);

            if (preg_match('/' . $this->getConfig()::ALIAS_REGEX . '/', $args[0])) {
                $args = array_slice($args, 1);
            }

            $kernel = $this->app->make(Kernel::class);

            $kernel->commands();

            $status = $kernel->handle($input = new ArgvInput($args), new ConsoleOutput());

            $kernel->terminate($input, $status);

            exit($status);
        });
    }

    /**
     * Retrieve the WP CLI configuration.
     *
     * @return array
     */
    protected function getConfig()
    {
        return WP_CLI::get_configurator();
    }
}
