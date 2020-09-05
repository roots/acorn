<?php

namespace Roots\Acorn\Console\Concerns;

use Symfony\Component\Process\Process;

trait Exec
{
    /**
     * Execute a process and return the status.
     *
     * @param  string|array $commands
     * @param  boolean      $output
     * @return mixed
     */
    public function exec($commands, $output = false)
    {
        if (! is_array($commands)) {
            $commands = explode(' ', $commands);
        }

        $process = new Process($commands);
        $process->run();

        if ($output) {
            return $process->getOutput();
        }

        return true;
    }
}
