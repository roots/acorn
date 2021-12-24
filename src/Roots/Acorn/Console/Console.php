<?php

namespace Roots\Acorn\Console;

use Illuminate\Support\Composer;

class Console extends Composer
{
    /**
     * Execute acorn clear-compiled command.
     *
     * @return int
     */
    public function clearCompiled(): int
    {
        return $this->acorn('clear-compiled');
    }

    /**
     * Execute acorn config:cache command.
     *
     * @return int
     */
    public function configCache(): int
    {
        return $this->acorn('config:cache');
    }

    /**
     * Execute acorn config:clear command.
     *
     * @return int
     */
    public function configClear(): int
    {
        return $this->acorn('config:clear');
    }

    /**
     * Execute acorn optimize command.
     *
     * @return int
     */
    public function optimize(): int
    {
        return $this->acorn('optimize');
    }

    /**
     * Execute acorn optimize:clear command.
     *
     * @return int
     */
    public function optimizeClear(): int
    {
        return $this->acorn('optimize:clear');
    }

    /**
     * Execute acorn package:discover command.
     *
     * @return int
     */
    public function packageDiscover(): int
    {
        return $this->acorn('package:discover');
    }

    /**
     * Execute acorn vendor:public command.
     *
     * @return int
     */
    public function vendorPublish(): int
    {
        return $this->acorn('vendor:publish');
    }

    /**
     * Execute acorn view:cache command.
     *
     * @return int
     */
    public function viewCache(): int
    {
        return $this->acorn('view:cache');
    }

    /**
     * Execute acorn view:clear command.
     *
     * @return int
     */
    public function viewClear(): int
    {
        return $this->acorn('view:clear');
    }

    /**
     * Execute acorn command.
     *
     * @param  array  $command
     * @return int
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
        if ($this->files->exists($this->workingPath . '/wp-cli.phar')) {
            return [$this->phpBinary(), 'wp-cli.phar'];
        }

        return ['wp'];
    }
}
