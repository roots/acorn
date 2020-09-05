<?php

namespace Roots\Acorn\Console;

use WP_CLI;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Roots\Acorn\Console\Kernel;
use Roots\Acorn\Exceptions\Handler;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use TypeError;

class AcornWpCliCommand
{
    protected $container;

    /**
     * wp-cli `acorn`.
     *
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * wp-cli `acorn` handler.
     *
     * @return exit
     * @throws TypeError
     * @throws BindingResolutionException
     */
    public function __invoke()
    {
        $config = WP_CLI::get_configurator();
        $args = array_slice($_SERVER['argv'], 1);

        if (preg_match('/' . $config::ALIAS_REGEX . '/', $args[0])) {
            $args = array_slice($args, 1);
        }

        $this->app->singleton(ExceptionHandler::class, Handler::class);

        $kernel = $this->app->make(Kernel::class);

        $kernel->commands();

        $status = $kernel->handle($input = new ArgvInput($args), new ConsoleOutput());

        $kernel->terminate($input, $status);

        exit($status);
    }
}
