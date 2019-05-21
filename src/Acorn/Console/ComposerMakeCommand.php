<?php

namespace Roots\Acorn\Console;

class ComposerMakeCommand extends GeneratorCommand
{
    /** @var string The type of class being generated. */
    protected $type = 'Composer';

    /** @var array List of views served by the composer */
    protected $views = [];

    /**
     * Create a new composer class
     *
     * ## OPTIONS
     *
     * <name>
     * : The name of the composer.
     *
     * [--views]
     * : List of views served by the composer
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

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return $this->replaceViews($stub, $this->views);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  array   $views
     * @return string
     */
    protected function replaceViews($stub, $views)
    {
        $views = implode("',\n        '", $views);
        return str_replace("'dummy-views'", empty($views) ? '//' : "'{$views}'", $stub);
    }
}
