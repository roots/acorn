<?php

namespace Roots\Acorn\Console\Commands;

use Composer\InstalledVersions;

use function Laravel\Prompts\confirm;

class AcornInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acorn:install
                            {--autoload : Install the Acorn autoload dump script}
                            {--init : Initialize Acorn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Acorn into the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->askToInstallScript();
        $this->askToInitialize();
        $this->askToStar();

        return static::SUCCESS;
    }

    /**
     * Ask to install the Acorn autoload dump script.
     */
    protected function askToInstallScript(): void
    {
        if (! $this->option('autoload') && $this->option('no-interaction')) {
            return;
        }

        if ($this->option('autoload') || confirm(
            label: 'Would you like to install the Acorn autoload dump script?',
            default: true,
        )) {
            $this->installAutoloadDump();
        }
    }

    /**
     * Install the Acorn autoload dump script.
     */
    protected function installAutoloadDump(): void
    {
        $path = InstalledVersions::getInstallPath('roots/acorn');
        $path = dirname($path, 5);

        $composer = "{$path}/composer.json";

        if (! file_exists($composer)) {
            return;
        }

        $configuration = json_decode(file_get_contents($composer), associative: true);

        $script = 'Roots\\Acorn\\ComposerScripts::postAutoloadDump';

        if (in_array($script, $configuration['scripts']['post-autoload-dump'] ?? [])) {
            return;
        }

        $configuration['scripts']['post-autoload-dump'] ??= [];
        $configuration['scripts']['post-autoload-dump'][] = $script;

        $configuration = str(json_encode($configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            ->replace('    ', '  ')
            ->append(PHP_EOL)
            ->toString();

        file_put_contents(
            $composer,
            $configuration,
        );
    }

    /**
     * Ask the user to initialize Acorn.
     */
    protected function askToInitialize(): void
    {
        if (! $this->option('init') && $this->option('no-interaction')) {
            return;
        }

        if ($this->option('init') || confirm(
            label: 'Would you like to initialize Acorn?',
            default: true,
        )) {
            $this->callSilent('acorn:init', ['--base' => $this->getLaravel()->basePath()]);
        }
    }

    /**
     * Ask the user to star the Acorn repository.
     */
    protected function askToStar(): void
    {
        if ($this->option('no-interaction')) {
            return;
        }

        if (confirm(
            label: '🎉 All done! Would you like to show love by starring Acorn on GitHub?',
            default: true,
        )) {
            match (PHP_OS_FAMILY) {
                'Darwin' => exec('open https://github.com/roots/acorn'),
                'Linux' => exec('xdg-open https://github.com/roots/acorn'),
                'Windows' => exec('start https://github.com/roots/acorn'),
            };

            $this->components->info('Thank you!');
        }
    }
}
