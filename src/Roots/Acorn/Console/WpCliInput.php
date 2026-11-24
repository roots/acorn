<?php

namespace Roots\Acorn\Console;

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
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
     *
     * The arguments are passed as tokens, so they're never re-parsed as a shell string.
     */
    public function make(array $args, array $options): ArgvInput
    {
        $tokens = array_values($args);

        foreach ($this->restoreJsonOption($args, $options) as $key => $value) {
            array_push($tokens, ...$this->optionTokens((string) $key, $value));
        }

        return new ArgvInput(['wp acorn', ...$tokens]);
    }

    /**
     * Get the tokens for an option parsed by WP-CLI.
     *
     * WP-CLI parses `--no-{key}` as `false`. As of WP-CLI 3.0, it parses `-x` as an option
     * and collects repeated options into an array.
     */
    protected function optionTokens(string $key, mixed $value): array
    {
        if (is_array($value)) {
            return array_merge(...array_map(fn ($item) => $this->optionTokens($key, $item), array_values($value)));
        }

        if ($value === false) {
            return ["--no-{$key}"];
        }

        if (strlen($key) === 1) {
            return $value === true ? ["-{$key}"] : ["-{$key}", (string) $value];
        }

        return $value === true ? ["--{$key}"] : ["--{$key}=" . $value];
    }

    /**
     * Restore the `--json` option that WP-CLI rewrites to `--format=json`.
     *
     * Only commands with a `--json` option and no `--format` option are changed.
     */
    protected function restoreJsonOption(array $args, array $options): array
    {
        if (($options['format'] ?? null) !== 'json' || ! method_exists($this->kernel, 'findCommand')) {
            return $options;
        }

        try {
            $name = (new ArgvInput(['wp acorn', ...array_values($args)]))->getFirstArgument();

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
}
