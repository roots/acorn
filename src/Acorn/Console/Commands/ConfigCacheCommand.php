<?php

namespace Roots\Acorn\Console\Commands;

use Roots\Acorn\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use LogicException;
use Throwable;

class ConfigCacheCommand extends Command
{
    /**
     * Create a cache file for faster configuration loading
     *
     * ## EXAMPLES
     *
     *     wp acorn config:cache
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function __invoke($args, $assoc_args)
    {
        $this->call('config:clear');

        $this->parse($assoc_args);

        $config = $this->getFreshConfiguration();

        $configPath = $this->app->getCachedConfigPath();

        $this->files->put(
            $configPath,
            '<?php return ' . var_export($config, true) . ';' . PHP_EOL
        );

        try {
            require $configPath;
        } catch (Throwable $e) {
            $this->files->delete($configPath);

            throw new LogicException('Your configuration files are not serializable.', 0, $e);
        }

        $this->success('Configuration cached successfully!');
    }

    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        return $this->app['config']->all();
    }
}
