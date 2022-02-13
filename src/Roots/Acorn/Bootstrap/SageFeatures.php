<?php

namespace Roots\Acorn\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

use function Roots\wp_die;

/**
 * @deprecated
 */
class SageFeatures
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (get_theme_support('sage')) {
            wp_die(
                sprintf(
                    "Your <a href=\"https://github.com/roots/sage/blob/main/app/Providers/ThemeServiceProvider.php\">ThemeServiceProvider</a> should extend [%s].",
                    \Roots\Acorn\Sage\SageServiceProvider::class,
                ),
                '<code>add_theme_support(\'sage\')</code> is not supported.',
                'Acorn &rsaquo; Boot Error',
                'Check out the <a href="https://github.com/roots/acorn/releases/tag/v2.0.0-beta.10">release notes</a> for more information.<br><br>This message will be removed with the next beta release of Acorn.'
            );
        }
    }
}
