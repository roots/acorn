<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand as GeneratorCommandBase;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Console\Concerns\ClearLine;
use Roots\Acorn\Console\Concerns\Exec;
use Roots\Acorn\Console\Concerns\Task;
use Roots\Acorn\Console\Concerns\Title;

abstract class GeneratorCommand extends GeneratorCommandBase
{
    use ClearLine;
    use Exec;
    use Task;
    use Title;

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
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace(
            ['DummySlug', 'DummyCamel', 'DummySnake'],
            [Str::slug($class), Str::camel($class), Str::snake($class)],
            parent::replaceClass($stub, $name)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->app->path() . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNameInput()
    {
        return trim(
            is_array($name = $this->argument('name')) ? array_pop($name) : $name
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function userProviderModel()
    {
        //
    }
}
