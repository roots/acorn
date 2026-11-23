<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\OptimizeClearCommand as FoundationOptimizeClearCommand;
use Roots\Acorn\Console\Concerns\GracefullyCallsCommands;

class OptimizeClearCommand extends FoundationOptimizeClearCommand
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
