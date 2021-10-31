<?php

namespace Roots\Acorn\Bootstrap;

use WP_CLI;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Console\Kernel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

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

        WP_CLI::add_command('acorn', function ($args, $assoc_args) {
            /** @var Kernel */
            $kernel = $this->app->make(Kernel::class);

            $kernel->commands();

            $input = $args;

            foreach ($assoc_args as $key => $value) {
                $input["--{$key}"] = $value;
            }

            if (in_array('help', $_SERVER['argv'])) {
                array_unshift($input, 'help');
            }

            $status = $kernel->handle($input = new ArrayInput($input), new ConsoleOutput());

            $kernel->terminate($input, $status);

            WP_CLI::halt($status);
        });
    }
}
