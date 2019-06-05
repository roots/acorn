<?php

namespace Roots\Acorn\Console;

use Illuminate\Support\Traits\Macroable;
use Roots\Acorn\Application;
use Roots\Acorn\Filesystem\Filesystem;

abstract class Command
{
    use Macroable;

    /** @var \Acorn\Application The Laravel application instance. */
    protected $app;

    /** @var \Illuminate\Filesystem\Filesystem The filesystem instance. */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param \Acorn\Application $app
     * @return void
     */
    public function __construct(Filesystem $files, Application $app)
    {
        $this->files = $files;
        $this->app = $app;
    }

    /**
     * Parse associated args from WP-CLI
     *
     * @param array $assoc_args
     */
    public function parse(array $assoc_args)
    {
        foreach ($assoc_args as $key => $value) {
            if (! property_exists($this, $key)) {
                continue;
            }
            $this->{$key} = is_array($this->{$key})
                ? explode(',', $value)
                : $value;
        }
    }

    /**
     * Entry point for WP-CLI.
     *
     * Consider using PHPDoc to annotate this method when implemented. The
     * annotations should describe the command and provide examples.
     *
     * @link https://make.wordpress.org/cli/handbook/commands-cookbook/#annotating-with-phpdoc
     *
     * @param array $args
     * @param array $assoc_args
     */
    abstract public function __invoke($args, $assoc_args);

    /**
     * Run a WP-CLI Acorn command
     *
     * @param string $command WP-CLI Acorn command to run, including arguments.
     * @param array $options Configuration options for command execution
     *
     * @return mixed
     */
    protected function call($command, $options = [])
    {
        return \WP_CLI::runcommand("acorn {$command}", $options);
    }

    /**
     * Display success message prefixed with "Success: ".
     *
     * Success message is written to STDOUT.
     *
     * Typically recommended to inform user of successful script conclusion.
     *
     * @param string $message Message to write to STDOUT.
     *
     * @return void
     */
    protected function success($message)
    {
        \WP_CLI::success($message);
    }

    /**
     * Display warning message prefixed with "Warning: ".
     *
     * Warning message is written to STDERR.
     *
     * @param string $message Message to write to STDOUT.
     *
     * @return void
     */
    protected function warning($message)
    {
        \WP_CLI::warning($message);
    }

    /**
     * Display error message prefixed with "Error: " and exit script.
     *
     * Error message is written to STDERR.
     *
     * @param string|array $message Message to write to STDOUT.
     * @param bool|int $exit Exit code for application
     *
     * @return void
     */
    protected function error($message, $exit = true)
    {
        \WP_CLI::{is_array($message) ? 'error_multi_line' : 'error'}($message);
    }

    /**
     * Display informational message without prefix, and ignore `--quiet`.
     *
     * Message is written to STDOUT.
     *
     * @param string $message Message to write to STDOUT.
     *
     * @return void
     */
    protected function line($message)
    {
        \WP_CLI::line($message);
    }

    /**
     * Display informational message without prefix.
     *
     * Message is written to STDOUT, or discarded when `--quiet` flag is supplied.
     *
     * @param string $message Message to write to STDOUT.
     *
     * @return void
     */
    protected function info($message)
    {
        \WP_CLI::log($message);
    }

    /**
     * Display debug message prefixed with "Debug: " when `--debug` is used.
     *
     * Debug message is written to STDERR, and includes script execution time.
     *
     * @param string $message Message to write to STDOUT.
     * @param string|bool $group Organize debug message to a specific group.
     *
     * @return void
     */
    protected function debug($message, $group = false)
    {
        \WP_CLI::debug($message, $group);
    }

    /**
     * Display success message prefixed with "Success: ".
     *
     * @param string $message Message to write to STDOUT.
     *
     * @return void
     */
    protected function choice($message, iterable $choices)
    {
        fwrite(STDOUT, "$message\n\n  ");
        foreach ($choices as $key => $value) {
            fwrite(STDOUT, "[{$key}] - {$value}\n  ");
        }
        fwrite(STDOUT, "\n  >");

        $answer = trim(fgets(STDIN));

        if (! isset($choices[$answer])) {
            $this->info("{$answer} is not a valid option.");
            return $this->choice($message, $choices);
        }

        return $choices[$answer];
    }
}
