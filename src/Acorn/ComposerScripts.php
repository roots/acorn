<?php

namespace Roots\Acorn;

use Composer\Script\Event;
use Roots\Acorn\Application;
use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\PackageManifest;

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
        $app = new Application(getcwd());

        $app->instance(PackageManifest::class, new PackageManifest(
            new Filesystem(),
            $app->basePath(),
            $app->getCachedPackagesPath()
        ));

        $app->make(PackageManifest::class)->build();
    }

    /**
     * Clear the cached Acorn bootstrapping files.
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $app = new Application(getcwd());

        if (file_exists($servicesPath = $app->getCachedServicesPath())) {
            @unlink($servicesPath);
        }

        if (file_exists($packagesPath = $app->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }
    }
}
