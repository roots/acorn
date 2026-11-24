<?php

namespace Roots\Acorn\Console;

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\StringInput;
use Throwable;

/**
 * @internal
 */
class WpCliInput
{
    /**
     * Create a new WP-CLI input builder.
     */
    public function __construct(
        protected Kernel $kernel,
    ) {
        //
    }

    /**
     * Build the console input for a `wp acorn` command.
     */
    public function make(array $args, array $options): StringInput
    {
        $command = implode(' ', array_map(fn ($arg) => escapeshellarg($arg), $args));

        $options = $this->restoreJsonOption($command, $options);

        foreach ($options as $key => $value) {
            if ($key === 'interaction' && $value === false) {
                $command .= ' --no-interaction';

                continue;
            }

            $command .= " --{$key}";

            if ($value !== true) {
                $command .= '=' . escapeshellarg($value);
            }
        }

        return $this->input($command);
    }

    /**
     * Restore the `--json` option that WP-CLI rewrites to `--format=json`.
     *
     * Only commands with a `--json` option and no `--format` option are changed.
     */
    protected function restoreJsonOption(string $command, array $options): array
    {
        if (($options['format'] ?? null) !== 'json' || ! method_exists($this->kernel, 'findCommand')) {
            return $options;
        }

        try {
            $name = $this->input($command)->getFirstArgument();

            $definition = $name ? $this->kernel->findCommand($name)?->getDefinition() : null;
        } catch (Throwable) {
            return $options;
        }

        if (! $definition?->hasOption('json') || $definition->hasOption('format')) {
            return $options;
        }

        unset($options['format']);

        return [...$options, 'json' => true];
    }

    /**
     * Create the console input for the given command string.
     */
    protected function input(string $command): StringInput
    {
        return new StringInput(str_replace('\\', '\\\\', $command));
    }
}
