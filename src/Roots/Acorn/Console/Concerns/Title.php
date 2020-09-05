<?php

namespace Roots\Acorn\Console\Concerns;

trait Title
{
    /**
     * Write a string in a title box.
     *
     * @param  string $title
     * @return $this
     */
    public function title($title)
    {
        $size = strlen($title);
        $spaces = str_repeat(' ', $size);

        $this->output->newLine();
        $this->output->writeln("<bg=blue;fg=white>{$spaces}{$spaces}{$spaces}</>");
        $this->output->writeln("<bg=blue;fg=white>{$spaces}{$title}{$spaces}</>");
        $this->output->writeln("<bg=blue;fg=white>{$spaces}{$spaces}{$spaces}</>");
        $this->output->newLine();

        return $this;
    }
}
