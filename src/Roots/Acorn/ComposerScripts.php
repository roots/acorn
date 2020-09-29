<?php

namespace Roots\Acorn;

use Composer\Script\Event;
use Roots\Acorn\Console\Console;
use Roots\Acorn\Filesystem\Filesystem;

class ComposerScripts
{
    /**
     * Handle the post-install Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postInstall(Event $event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';

        static::clearCompiled();
        static::buildManifest();
    }

    /**
     * Handle the post-update Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postUpdate(Event $event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';

        static::clearCompiled();
        static::buildManifest();
    }

    /**
     * Handle the post-autoload-dump Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postAutoloadDump(Event $event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';

        static::clearCompiled();
        static::buildManifest();
    }

    /**
     * Build the Acorn package manifest and write it to disk.
     *
     * @return void
     */
    protected static function buildManifest()
    {
        $console = new Console(new Filesystem(), getcwd());

        $console->packageDiscover();
    }

    /**
     * Clear the cached Acorn bootstrapping files.
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $console = new Console(new Filesystem(), getcwd());

        $console->packageClear();
    }
}
