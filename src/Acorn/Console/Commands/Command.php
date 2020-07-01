<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Console\Command as CommandBase;

abstract class Command extends CommandBase
{
    use \Roots\Acorn\Console\Concerns\ClearLine;
    use \Roots\Acorn\Console\Concerns\Exec;
    use \Roots\Acorn\Console\Concerns\Task;
    use \Roots\Acorn\Console\Concerns\Title;

    /**
     * The application implementation.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    public function setLaravel($laravel)
    {
        parent::setLaravel($this->app = $laravel);
    }
}
