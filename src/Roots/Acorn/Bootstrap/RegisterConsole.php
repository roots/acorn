<?php

namespace Roots\Acorn\Bootstrap;

use WP_CLI;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Console\Kernel;
use Symfony\Component\Console\Input\StringInput;
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

            $command = implode(' ', $args);

            foreach ($assoc_args as $key => $value) {
                $command .= " {$this->formatOption($key, $value)}";
            }

            $command = str_replace('\\', '\\\\', $command);

            $status = $kernel->handle($input = new StringInput($command), new ConsoleOutput());

            $kernel->terminate($input, $status);

            WP_CLI::halt($status);
        });
    }

    /**
     * Formats and escapes argument for StringInput.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    protected function formatOption($key, $value)
    {
        if (is_bool($value)) {
            return $value ? "--{$key}" : "--no-{$key}";
        }

        return "--{$key}='{$value}'";
    }
}
