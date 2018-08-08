<?php

namespace Roots\Acorn;

use Illuminate\Config\Repository as ConfigBase;

class Config extends ConfigBase
{
    /** @var string[] List of directory paths containing configuration files */
    public $paths = [];

    /**
     * Locate and merge a config file
     *
     * @param string $path
     * @param string $key
     */
    public function load($path, $key = null)
    {
        $key    = ($key ?? basename($path, '.php'));
        $config = $this->get($key, []);
        $this->set($key, array_merge($config, require $path, require $this->locate($path)));
    }

    /**
     * Locate config file based on specified paths
     *
     * @param string $path Path to a config file
     */
    protected function locate($path)
    {
        $file = basename($path);

        foreach (array_unique($this->paths) as $dir) {
            if (file_exists("{$dir}/{$file}")) {
                return "{$dir}/{$file}";
            }
        }

        return $path;
    }
}
