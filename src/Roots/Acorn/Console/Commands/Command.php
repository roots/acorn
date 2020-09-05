<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Console\Command as CommandBase;
use Roots\Acorn\Application;
use Roots\Acorn\Console\Concerns\ClearLine;
use Roots\Acorn\Console\Concerns\Exec;
use Roots\Acorn\Console\Concerns\Task;
use Roots\Acorn\Console\Concerns\Title;

abstract class Command extends CommandBase
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
}
