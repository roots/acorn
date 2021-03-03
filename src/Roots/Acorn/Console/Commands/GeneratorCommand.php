<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Console\GeneratorCommand as GeneratorCommandBase;

abstract class GeneratorCommand extends GeneratorCommandBase
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    public function setLaravel($laravel)
    {
        parent::setLaravel($this->app = $laravel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getNameInput()
    {
        return trim(
            is_array($name = $this->argument('name')) ? end($name) : $name
        );
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this
            ->replaceIlluminate($stub)
            ->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the Illuminate namespace with Acorn for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceIlluminate(&$stub)
    {
        $stub = str_replace(
            'Illuminate\\Support\\',
            'Roots\\Acorn\\',
            $stub
        );

        return $this;
    }
}
