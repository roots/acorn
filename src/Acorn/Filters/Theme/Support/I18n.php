<?php

namespace Roots\Acorn\Filters\Theme\Support;

use Roots\Acorn\Application;
use Roots\Acorn\Support\Filter;

use function Roots\logger as logger;
use function Roots\storage_path as storage_path;

class I18n extends Filter
{
    /**
     * Application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * Default languages directory name.
     *
     * @var string
     */
    protected $defaultLanguagesDirectory = 'languages';

    /**
     * Default theme text domain.
     *
     * @var string
     */
    protected $defaultTextDomain = 'sage';

    /**
     * The filter tag it responds to.
     *
     * @var string
     */
    protected $tag = 'after_setup_theme';

    /**
     * I18n filter constructor.
     *
     * @param \Roots\Acorn\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Load text domain for theme.
     *
     * @see https://developer.wordpress.org/reference/functions/load_theme_textdomain/
     * @return void
     */
    public function handle(): void
    {
        // if function for loading text domain, does not exists
        // stop filter and warn by logs
        if (!function_exists('load_theme_textdomain')) {
            logger()->warning('Function load_theme_textdomain not found. I18n support is unavailable.');
            return;
        }

        load_theme_textdomain(
            $this->getTextDomain(),
            $this->getLanguagesDirectoryPath()
        );
    }

    /**
     * Get text domain used for i18n.
     *
     * @return string
     */
    protected function getTextDomain(): string
    {
        $themeTextDomain = $this->getThemeTextDomain();

        // if theme text domain does not exists
        // assume it is a default value
        if (empty($themeTextDomain)) {
            $themeTextDomain = $this->defaultTextDomain;
        }

        // allow developers to modify
        return apply_filters(
            'acorn/filters/theme/support/i18n/text_domain',
            $themeTextDomain
        );
    }

    /**
     * Get theme text domain.
     * If theme does not have it, return null.
     *
     * @see https://developer.wordpress.org/reference/functions/wp_get_theme/
     * @return string|null
     */
    protected function getThemeTextDomain(): ?string
    {
        // check if can get theme details
        if (!function_exists('wp_get_theme')) {
            return null;
        }

        // try to get theme text domain,
        // return null on failure
        $theme = wp_get_theme();
        return $theme->get('TextDomain') ?? null;
    }

    /**
     * Get languages directory path.
     * This directory must include generated theme *.pot files.
     *
     * @return string
     */
    protected function getLanguagesDirectoryPath(): string
    {
        // we do not need to apply filter on this value
        // because is config value, it can be easily modified
        return $this->app->config->get(
            'i18n.languages_path',
            storage_path($this->defaultLanguagesDirectory)
        );
    }
}
