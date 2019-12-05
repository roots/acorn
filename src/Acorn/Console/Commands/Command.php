<?php

namespace Roots\Acorn\Console\Commands;

use Symfony\Component\Process\Process;
use Illuminate\Support\Str;
use Illuminate\Console\Command as CommandBase;

abstract class Command extends CommandBase
{
    /**
     * The application implementation.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    public function setLaravel($laravel)
    {
        parent::setLaravel($this->app = $laravel);
    }

    /**
     * Write a string in a title box.
     *
     * @param  string $title
     * @return \Roots\Acorn\Console\Commands\Command
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

    /**
     * Execute a process and return the status.
     *
     * @param  string|array $commands
     * @param  boolean      $output
     * @return mixed
     */
    protected function exec($commands, $output = false)
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
