<?php

namespace Roots\Acorn\Console;

use Roots\Acorn\PackageManifest;

class PackageDiscoverCommand extends Command
{
    /**
     * Discover and publish vendor packages.
     *
     * ## EXAMPLES
     *
     *     wp acorn package:discover
     *
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        $this->handle();
    }

    /**
     * Return the package manifest.
     *
     * @return \Roots\Acorn\PackageManifest
     */
    public function handle(PackageManifest $manifest)
    {
        $manifest->build();

        foreach (array_keys($manifest->manifest) as $package) {
            $this->line("Discovered Package: <info>{$package}</info>");
        }

        $this->info('Package manifest generated successfully.');
    }
}
