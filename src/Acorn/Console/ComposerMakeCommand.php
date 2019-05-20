<?php

namespace Roots\Acorn\Console;

class ComposerMakeCommand extends GeneratorCommand
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Composer';

    /**
     * Create a new composer class
     *
     * ## OPTIONS
     *
     * <name>
     * : The name of the composer.
     *
     * [--force]
     * : Overwrite any existing files
     *
     * ## EXAMPLES
     *
     *     wp acorn make:composer
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
        return __DIR__ . '/stubs/composer.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Composers';
    }
}
