<?php

namespace Roots\Acorn\Console\Concerns;

use Illuminate\Support\Str;

trait Task
{
    /**
     * Run a task in the console.
     *
     * @param  string        $title
     * @param  callable|null $task
     * @param  string        $status
     * @return mixed
     */
    protected function task($title, $task = null, $status = '...')
    {
        $title = Str::start($title, '<fg=blue;options=bold>➡</> ');

        if (! $task) {
            return $this->output->write("{$title}: <info>✔</info>");
        }

        $this->output->write("{$title}: <comment>{$status}</comment>");

        try {
            $status = $task() !== false;
        } catch (\Exception $e) {
            $this->clearLine()->line("{$title}: <fg=red;options=bold>x</>");

            throw $e;
        }

        $this->clearLine()->line("{$title}: " . ($status ? '<info>✔</info>' : '<fg=red;options=bold>x</>'));
    }
}
