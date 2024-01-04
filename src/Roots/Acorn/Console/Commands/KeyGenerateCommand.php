<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Console\KeyGenerateCommand as FoundationKeyGenerateCommand;
use Illuminate\Support\Facades\File;

class KeyGenerateCommand extends FoundationKeyGenerateCommand
{
    /**
     * Write a new environment file with the given key.
     *
     * @param  string  $key
     * @return bool
     */
    protected function writeNewEnvironmentFileWith($key)
    {
        $envFile = file_exists($this->laravel->environmentFilePath())
            ? $this->laravel->environmentFilePath()
            : File::closest($this->laravel->basePath(), '.env');

        if (! $envFile) {
            $this->error('Unable to set application key. Create a .env file.');

            return false;
        }

        $replaced = preg_replace(
            $this->keyReplacementPattern(),
            'APP_KEY='.$key,
            $input = file_get_contents($envFile)
        );

        if ($replaced === $input || $replaced === null) {
            $this->error('Unable to set application key. No APP_KEY variable was found in the .env file.');

            return false;
        }

        file_put_contents($envFile, $replaced);

        return true;
    }
}
