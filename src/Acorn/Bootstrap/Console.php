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

        if ($this->app->runningInConsole()) {
            WP_CLI::add_command('acorn', function () {
                $args = [];

                if (! empty($_SERVER['argv'])) {
                    $args = array_slice($_SERVER['argv'], 2);
                    array_unshift($args, $_SERVER['argv'][0]);
                }

                $this->app->singleton(
                    \Illuminate\Contracts\Debug\ExceptionHandler::class,
                    \Roots\Acorn\Exceptions\Handler::class
                );

                $kernel = $this->app->make(\Roots\Acorn\Console\Kernel::class);

                $kernel->commands();

                $status = $kernel->handle(
                    $input = new \Symfony\Component\Console\Input\ArgvInput($args),
                    new \Symfony\Component\Console\Output\ConsoleOutput()
                );

                $kernel->terminate($input, $status);

                exit($status);
            });
        }
    }
}
