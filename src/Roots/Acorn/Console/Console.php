<?php

namespace Roots\Acorn\Console;

use Illuminate\Support\Composer;

class Console extends Composer
{
    /**
     * Execute acorn clear-compiled command.
     */
    public function clearCompiled(): int
    {
        return $this->acorn('clear-compiled');
    }

    /**
     * Execute acorn config:cache command.
     */
    public function configCache(): int
    {
        return $this->acorn('config:cache');
    }

    /**
     * Execute acorn config:clear command.
     */
    public function configClear(): int
    {
        return $this->acorn('config:clear');
    }

    /**
     * Execute acorn optimize command.
     */
    public function optimize(): int
    {
        return $this->acorn('optimize');
    }

    /**
     * Execute acorn optimize:clear command.
     */
    public function optimizeClear(): int
    {
        return $this->acorn('optimize:clear');
    }

    /**
     * Execute acorn package:discover command.
     */
    public function packageDiscover(): int
    {
        return $this->acorn('package:discover');
    }

    /**
     * Execute acorn vendor:public command.
     */
    public function vendorPublish(): int
    {
        return $this->acorn('vendor:publish');
    }

    /**
     * Execute acorn view:cache command.
     */
    public function viewCache(): int
    {
        return $this->acorn('view:cache');
    }

    /**
     * Execute acorn view:clear command.
     */
    public function viewClear(): int
    {
        return $this->acorn('view:clear');
    }

    /**
     * Execute acorn command.
     *
     * @param  array  $command
     */
    public function acorn($command): int
    {
        $command = array_merge($this->findWpCli(), ['acorn'], (array) $command);

        return $this->getProcess($command)->run();
    }

    /**
     * Get the wp-cli command for the environment.
     *
     * @return array
     */
    protected function findWpCli()
    {
        if ($this->files->exists($this->workingPath.'/wp-cli.phar')) {
            return [$this->phpBinary(), 'wp-cli.phar'];
        }

        return ['wp'];
    }
}
