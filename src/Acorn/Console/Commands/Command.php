<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Console\Command as CommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Console\Parser;

abstract class Command extends CommandBase
{
    use \Roots\Acorn\Console\Concerns\ClearLine;
    use \Roots\Acorn\Console\Concerns\Exec;
    use \Roots\Acorn\Console\Concerns\Task;
    use \Roots\Acorn\Console\Concerns\Title;

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
     * Configure the console command using a fluent definition.
     *
     * @return void
     */
    protected function configureUsingFluentDefinition()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);
        
        // Add option --wp-blog to all signatures
        $options[] = new InputOption(
            'wp-blog',
            null, // shortcut
            InputOption::VALUE_OPTIONAL,
            'For multsite environments, either a Blog ID or Blog Domain to switch to before execution',
            null // default
        );

        $this->setDefinition(new InputDefinition());

        if (null !== $name || null !== $name = static::getDefaultName()) {
            $this->setName($name);
        }

        $this->configure();

        // After parsing the signature we will spin through the arguments and options
        // and set them on this command. These will already be changed into proper
        // instances of these "InputArgument" and "InputOption" Symfony classes.
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }
    
    /**
     * Execute the console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('wp-blog')) {
            $blog = $input->getOption('wp-blog');
            if (!is_numeric($blog)) {
                $blog = get_blog_id_from_url($blog);   
            }
            switch_to_blog($blog);
        }
        return (int) $this->laravel->call([$this, 'handle']);
    }
}
