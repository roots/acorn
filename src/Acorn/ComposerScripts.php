<?php

namespace Roots\Acorn;

use Composer\Autoload\ClassLoader;
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
        $loader = require $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
        $basePath = static::getAppBasePath($loader);

        static::clearCompiled($basePath);
        static::buildManifest($basePath);
    }

    /**
     * Handle the post-update Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postUpdate(Event $event)
    {
        $loader = require $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
        $basePath = static::getAppBasePath($loader);

        static::clearCompiled($basePath);
        static::buildManifest($basePath);
    }

    /**
     * Handle the post-autoload-dump Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postAutoloadDump(Event $event)
    {
        $loader = require $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
        $basePath = static::getAppBasePath($loader);

        static::clearCompiled($basePath);
        static::buildManifest($basePath);
    }

    /**
     * Build the Acorn package manifest and write it to disk.
     *
     * @param  string   $basePath
     * @return void
     */
    protected static function buildManifest($basePath)
    {
        $app = new Application($basePath);

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
     * @param  string   $basePath
     * @return void
     */
    protected static function clearCompiled($basePath)
    {
        $app = new Application($basePath);

        if (file_exists($servicesPath = $app->getCachedServicesPath())) {
            @unlink($servicesPath);
        }

        if (file_exists($packagesPath = $app->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }
    }

    /**
     * Get base path for Application registered by App\ namespace or fallback to current dir
     *
     * @param  \Composer\Autoload\ClassLoader   $loader
     * @return string
     */
    protected static function getAppBasePath(ClassLoader $loader)
    {
        $psr4 = $loader->getPrefixesPsr4();

        if (!empty($psr4['App\\']) && ($namespacePath = array_shift($psr4['App\\']))) {
            return dirname($namespacePath);
        }

        return getcwd();
    }
}
