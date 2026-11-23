<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\OptimizeCommand as FoundationOptimizeCommand;
use Roots\Acorn\Console\Concerns\GracefullyCallsCommands;

class OptimizeCommand extends FoundationOptimizeCommand
{
    use GracefullyCallsCommands;

    /**
     * Call another console command without output.
     *
     * Silently skip the command if it does not exist.
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @return int
     */
    public function callSilent($command, array $arguments = [])
    {
        return $this->gracefulCallSilent($command, $arguments);
    }
}
