<?php

namespace Roots\Acorn\Console;

use Roots\Acorn\Filesystem\Filesystem;

class ProviderMakeCommand extends GeneratorCommand
{
    /** @var string The type of class being generated */
    protected $type = 'Provider';

    /**
     * Create a new service provider class
     *
     * ## OPTIONS
     *
     * <name>
     * : The name of the provider.
     *
     * [--force]
     * : Overwrite any existing files
     *
     * ## EXAMPLES
     *
     *     wp acorn make:provider
     *
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        list($name) = $args;
        $this->parse($assoc_args + compact('name'));
        $this->handle();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/provider.stub';
    }


    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Providers';
    }
}
