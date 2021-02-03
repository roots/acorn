<?php

namespace Roots\Acorn\Console;

use Illuminate\Support\Composer;

class Console extends Composer
{
    /**
     * Execute acorn config:cache command.
     *
     * @return void
     */
    public function configCache()
    {
        $this->acorn('config:cache');
    }

    /**
     * Execute acorn config:clear command.
     *
     * @return void
     */
    public function configClear()
    {
        $this->acorn('config:clear');
    }

    /**
     * Execute acorn optimize command.
     *
     * @return void
     */
    public function optimize()
    {
        $this->acorn('optimize');
    }

    /**
     * Execute acorn optimize:clear command.
     *
     * @return void
     */
    public function optimizeClear()
    {
        $this->acorn('optimize:clear');
    }

    /**
     * Execute acorn package:clear command.
     *
     * @return void
     */
    public function packageClear()
    {
        $this->acorn('package:clear');
    }

    /**
     * Execute acorn package:discover command.
     *
     * @return void
     */
    public function packageDiscover()
    {
        $this->acorn('package:discover');
    }

    /**
     * Execute acorn vendor:public command.
     *
     * @return void
     */
    public function vendorPublish()
    {
        $this->acorn('vendor:publish');
    }

    /**
     * Execute acorn view:cache command.
     *
     * @return void
     */
    public function viewCache()
    {
        $this->acorn('view:cache');
    }

    /**
     * Execute acorn view:clear command.
     *
     * @return void
     */
    public function viewClear()
    {
        $this->acorn('view:clear');
    }

    /**
     * Execute acorn command.
     *
     * @param  array  $command
     * @return void
     */
    public function acorn($command)
    {
        $command = array_merge($this->findWpCli(), ['acorn'], (array) $command);

        $this->getProcess($command)->run();
    }

    /**
     * Get the wp-cli command for the environment.
     *
     * @return array
     */
    protected function findWpCli()
    {
        if ($this->files->exists($this->workingPath . '/wp-cli.phar')) {
            return [$this->phpBinary(), 'wp-cli.phar'];
        }

        return ['wp'];
    }
}
