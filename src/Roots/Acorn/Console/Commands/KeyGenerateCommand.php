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
        $envFile = $this->laravel->environmentFilePath();

        if (! $envFile) {
            $this->error('Unable to set application key. Create a .env file.');

            return false;
        }

        if ($this->replace($envFile, $key)) {
            return true;
        }

        if ($this->prepend($envFile, $key)) {
            return true;
        }

        $this->error('Unable to set application key.');

        return false;
    }

    protected function replace($envFile, $key): bool
    {
        $replaced = preg_replace(
            $this->keyReplacementPattern(),
            'APP_KEY='.$key,
            $input = file_get_contents($envFile)
        );

        if ($replaced === $input || $replaced === null) {
            return false;
        }

        return file_put_contents($envFile, $replaced) !== false;
    }

    protected function prepend($envFile, $key): bool
    {
        return File::prepend($envFile, 'APP_KEY='.$key.PHP_EOL) !== false;
    }
}
