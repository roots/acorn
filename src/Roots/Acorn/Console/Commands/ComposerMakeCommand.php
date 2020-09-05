<?php

namespace Roots\Acorn\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

class ComposerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:composer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view composer class';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Composer';

    /**
     * List of views served by the composer
     *
     * @var array
     */
    protected $views = [];

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
        return $rootNamespace . '\View\Composers';
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

        return $this->replaceViews($stub, explode(' ', $this->option('views')));
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
        return str_replace('DummyViews', empty($views) ? '//' : "'{$views}'", $stub);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the view composer already exists'],
            ['views', null, InputOption::VALUE_NONE, 'Create a view composer with a pre-defined set of views'],
        ];
    }
}
