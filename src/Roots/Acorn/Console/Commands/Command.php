<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Console\Command as CommandBase;
use Illuminate\Contracts\Foundation\Application;

abstract class Command extends CommandBase
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
}
