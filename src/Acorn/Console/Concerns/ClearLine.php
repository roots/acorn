<?php

namespace Roots\Acorn\Console\Concerns;

trait ClearLine
{
    /**
     * Clear the current line in console output.
     *
     * @return \Roots\Acorn\Console\Commands\Command
     */
    public function clearLine()
    {
        if (! $this->output->isDecorated()) {
            $this->output->writeln('');

            return $this;
        }

        $this->output->write("\x0D");
        $this->output->write("\x1B[2K");

        return $this;
    }
}
