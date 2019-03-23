<?php

use function Roots\env;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Acorn is shown below to make development simple.
    |
    |
    | All database work in Acorn is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', defined('DB_HOST') ? DB_HOST : 'localhost'),
            'port' => env('DB_PORT', defined('DB_PORT') ? DB_PORT : '3306'),
            'database' => env('DB_NAME', defined('DB_NAME') ? DB_NAME : 'wordpress'),
            'username' => env('DB_USER', defined('DB_USER') ? DB_USER : 'wordpress'),
            'password' => env('DB_PASSWORD', defined('DB_PASSWORD') ? DB_PASSWORD : ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'),
            'collation' => env('DB_COLLATE', defined('DB_COLLATE') ? DB_COLLATE : 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', $GLOBALS['table_prefix'] ?? 'wp_'),
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

];
