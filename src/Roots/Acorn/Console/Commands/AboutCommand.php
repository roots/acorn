<?php

namespace Roots\Acorn\Console\Commands;

use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\Console\AboutCommand as BaseCommand;
use Illuminate\Support\Str;
use ReflectionFunction;
use Roots\Acorn\Application;

class AboutCommand extends BaseCommand
{
    protected function gatherApplicationInformation()
    {
        parent::gatherApplicationInformation();

        $this->aboutEnvironment();
    }

    /**
     * Add environment information.
     */
    protected function aboutEnvironment()
    {
        // Laravel does not provide an easy way to override a section
        // so we have to unset it and then add it back, and then
        // re-execute any custom environment resolvers.
        unset(self::$data['Environment']);

        static::addToSection('Environment', fn () => [
            'Application Name' => $this->laravel->get('config')->get('app.name'),
            'PHP' => $this->formatVersion(PHP_VERSION),
            'Xdebug' => $this->formatVersion(phpversion('xdebug')),
            'Composer' => $this->formatVersion($this->composer->getVersion()),
            'Acorn' => $this->formatVersion(Application::VERSION),
            'Laravel' => $this->formatVersion(FoundationApplication::VERSION),
            'WordPress' => $this->formatVersion(get_bloginfo('version')),
            'WP-CLI' => $this->formatVersion(defined('WP_CLI_VERSION') ? WP_CLI_VERSION : null),
            'Environment' => $this->laravel->environment(),
            'Debug Mode' => $this->laravel->get('config')->get('app.debug') ? '<fg=yellow;options=bold>ENABLED</>' : 'OFF',
            'Maintenance Mode' => $this->laravel->isDownForMaintenance() ? '<fg=yellow;options=bold>ENABLED</>' : 'OFF',
            'URL' => Str::of(config('app.url'))->replace(['http://', 'https://'], ''),
            'Plugins' => get_site_transient('update_plugins') && count(get_site_transient('update_plugins')->response ?? []) ? '<fg=yellow;options=bold>UPDATES AVAILABLE</>' : '<fg=green;options=bold>UP TO DATE</>',
            'Themes' => get_site_transient('update_themes') && count(get_site_transient('update_themes')->response ?? []) ? '<fg=yellow;options=bold>UPDATES AVAILABLE</>' : '<fg=green;options=bold>UP TO DATE</>',
        ]);

        collect(static::$customDataResolvers)
            ->filter('is_callable')
            ->filter(fn ($resolver) => (new ReflectionFunction($resolver))->getClosureUsedVariables()['section'] === 'Environment')
            ->each->__invoke();
    }

    protected function formatVersion($version)
    {
        return $version ? ('v'.ltrim($version, 'vV')) : '<fg=yellow;options=bold>-</>';
    }
}
