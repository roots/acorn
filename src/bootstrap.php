<?php

use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Config;
use function Roots\app;
use function Roots\config;

/** Initiate facades */
Facade::clearResolvedInstances();
Facade::setFacadeApplication(app());

/** Bind a config instance */
app()->singleton('config', function () {
    $config = new Config();
    $config->paths = array_unique([
        get_theme_file_path('/config'),
        get_parent_theme_file_path('/config')
    ]);
    return $config;
});

/** Load configs */
app()->booting(function () {
    $config = config();
    $config->load(dirname(__DIR__) . '/config/app.php');
    $config->load(dirname(__DIR__) . '/config/assets.php');
    $config->load(dirname(__DIR__) . '/config/filesystems.php');
    $config->load(dirname(__DIR__) . '/config/view.php');
});

/** Register configured service providers */
app()->booting(function () {
    array_map([app(), 'register'], config('app.providers'));
});

/** Register autoloader for application aliases (formerly "Facades") */
app()->booting(function () {
    spl_autoload_register(function ($alias) {
        $aliases = config('app.aliases');
        if (isset($aliases[$alias])) {
            return \class_alias($aliases[$alias], $alias);
        }
    }, true, true);
});
