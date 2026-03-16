<?php

namespace Roots\Acorn\Bootstrap;

use Dotenv\Exception\InvalidFileException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as FoundationLoadEnvironmentVariables;

class LoadEnvironmentVariables extends FoundationLoadEnvironmentVariables
{
    /**
     * Bootstrap the given application.
     */
    public function bootstrap(Application $app): void
    {
        if ($app->configurationIsCached()) {
            return;
        }

        $this->checkForSpecificEnvironmentFile($app);

        if (! is_file($app->environmentPath().'/'.$app->environmentFile())) {
            return;
        }

        try {
            $this->createDotenv($app)->safeLoad();
        } catch (InvalidFileException $e) {
            $this->writeErrorAndDie($e);
        }
    }
}
