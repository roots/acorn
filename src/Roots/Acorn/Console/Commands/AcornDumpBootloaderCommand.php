<?php

namespace Roots\Acorn\Console\Commands;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Roots\Acorn\Bootloader;
use Roots\Acorn\Console\Commands\Command;
use Roots\Acorn\Filesystem\Filesystem;

class AcornDumpBootloaderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acorn:dump-bootloader {file=' . WPMU_PLUGIN_DIR . '/acorn-bootloader.php : Path to write bootloader.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bypass path discovery to quickly boot Acorn.';

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $file = $this->argument('file');

        if (! is_writable(dirname($file))) {
            throw new Exception('The ' . dirname($file) . ' directory must be present and writable.');
        }

        $bootloaderClass = get_class(Bootloader::getInstance());
        $applicationClass = get_class(Bootloader::getInstance()->getApplication());

        $app = (new $bootloaderClass())->getApplication();

        $this->files->put($file, $this->eject($app, $bootloaderClass, $applicationClass), true);
    }

    protected function eject(Application $app, $bootloaderClass, $applicationClass)
    {
        $use_paths = var_export($this->getApplicationPaths($app), true);

        return <<<PHP_CODE
        <?php

        {$this->frontmatter($app)}

        try {
            (new \\{$bootloaderClass}(new \\{$applicationClass}('{$app->basePath()}', {$use_paths})))->boot();
        } catch (\\Throwable \$e) {
            \\Roots\\bootloader()->boot();
            report(new \\Exception("Acorn recovered from a boot failure. Acorn failed to boot for an unknown reason. Please run `wp acorn acorn:dump-bootloader` or delete [" . __FILE__ . "].", 0, \$e));
        }
        PHP_CODE;
    }

    protected function frontmatter(Application $app)
    {
        $version = get_class($app)::VERSION ?? $app->version();

        return <<<FRONTMATTER
        /**
         * Plugin Name:   Acorn Bootloader
         * Plugin URI:    https://roots.io/acorn
         * Description:   Automatically boot Acorn framework.
         * Version:       {$version}
         * Author:        Roots
         * Author URI:    https://roots.io
         * License:       MIT
         * License URI:   http://opensource.org/licenses/MIT
         */
        FRONTMATTER;
    }

    protected function getApplicationPaths(Application $app)
    {
        return [
            'app' => method_exists($app, 'path') ? $app->path() : $app->make('path'),
            'lang' => method_exists($app, 'langPath') ? $app->langPath() : $app->make('path.lang'),
            'config' => $app->configPath(),
            'public' => method_exists($app, 'publicPath') ? $app->publicPath() : $app->make('path.public'),
            'storage' => $app->storagePath(),
            'database' => $app->databasePath(),
            'resources' => $app->resourcePath(),
            'bootstrap' => $app->bootstrapPath(),
        ];
    }
}
