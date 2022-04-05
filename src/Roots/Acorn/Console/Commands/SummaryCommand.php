<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Contracts\Container\Container;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SummaryCommand extends ListCommand
{
    /**
     * The application implementation.
     *
     * @var Container
     */
    protected $app;

    /**
     * The application binary.
     *
     * @var string
     */
    protected $binary = 'wp acorn';

    /**
     * The supported format.
     *
     * @return string
     */
    protected $format = 'txt';

   /**
     * The command name width.
     *
     * @var int
     */
    protected $width = 0;

    /**
     * Create a new Summary command instance.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        parent::__construct('list');

        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('format') === $this->format && ! $input->getOption('raw')) {
            $this->title($output)->usage($output)->commands($output);

            return 0;
        }

        return parent::execute($input, $output);
    }

    /**
     * Write the application title to console.
     *
     * @return $this
     */
    protected function title(OutputInterface $output)
    {
        $output->write(
            "\n  <fg=blue;options=bold>{$this->getApplication()->getVersion()}</>\n\n"
        );

        return $this;
    }

    /**
     * Write the command usage to console.
     *
     * @return $this;
     */
    protected function usage(OutputInterface $output)
    {
        $output->write("  <fg=blue;options=bold>USAGE:</> {$this->binary} <command> [options] [arguments]\n");

        return $this;
    }

    /**
     * Write the command list to console.
     *
     * @return $this
     */
    protected function commands(OutputInterface $output)
    {
        $this->width = 0;

        $namespaces = collect($this->getApplication()->all())->filter(function ($command) {
            return ! $command->isHidden();
        })->groupBy(function ($command) {
            $nameParts = explode(':', $name = $command->getName());

            $this->width = max($this->width, mb_strlen($name));

            return isset($nameParts[1]) ? $nameParts[0] : '';
        })->sortKeys()->each(function ($commands) use ($output) {
            $output->write("\n");

            $commands = $commands->toArray();

            usort($commands, function ($a, $b) {
                return $a->getName() > $b->getName() ? 1 : -1;
            });

            foreach ($commands as $command) {
                $output->write(sprintf(
                    "  <fg=blue>%s</>%s%s\n",
                    $command->getName(),
                    str_repeat(' ', $this->width - mb_strlen($command->getName()) + 1),
                    $command->getDescription()
                ));
            }
        });

        return $this;
    }
}
