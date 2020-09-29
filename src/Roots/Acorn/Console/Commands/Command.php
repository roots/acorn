<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Console\Command as CommandBase;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Console\Concerns\{ClearLine, Exec, Task, Title};

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
