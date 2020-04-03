<?php

namespace Roots\Acorn\Bootstrap;

use WP_CLI;
use Roots\Acorn\Application;

class Console
{
    /**
     * The application implementation.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        if ($this->app->runningInConsole() && class_exists('WP_CLI')) {
            WP_CLI::add_command('acorn', function () {
                $config = WP_CLI::get_configurator();
                [$args] = $config->parse_args($_SERVER['argv']);

                if (preg_match('/' . $config::ALIAS_REGEX . '/', $args[1])) {
                    array_splice($args, 1, 1);
                }

                $this->app->singleton(
                    \Illuminate\Contracts\Debug\ExceptionHandler::class,
                    \Roots\Acorn\Exceptions\Handler::class
                );

                $kernel = $this->app->make(\Roots\Acorn\Console\Kernel::class);

                $kernel->commands();

                $status = $kernel->handle(
                    $input = new \Symfony\Component\Console\Input\ArgvInput(array_slice($args, 2)),
                    new \Symfony\Component\Console\Output\ConsoleOutput()
                );

                $kernel->terminate($input, $status);

                exit($status);
            });
        }
    }
}
