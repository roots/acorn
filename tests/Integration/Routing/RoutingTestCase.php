<?php

namespace Roots\Acorn\Tests\Integration\Routing;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Roots\Acorn\Tests\Test\Concerns\SupportsGlobalStubs;
use Roots\Acorn\Tests\Test\Concerns\SupportsScopedFixtures;

class RoutingTestCase extends MockeryTestCase
{
    use MakesHttpRequests;
    use SupportsGlobalStubs;
    use SupportsScopedFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearStubs();

        // Ensure routes directory exists
        if (! is_dir('/roots/app/public/routes')) {
            mkdir('/roots/app/public/routes', 0777, true);
        }

        // Create web.php routes file
        $webRoutes = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/test', fn() => 'Howdy')->name('test');
});
PHP;

        file_put_contents('/roots/app/public/routes/web.php', $webRoutes);

        // Ensure mu-plugins directory exists
        if (! is_dir('/roots/app/public/content/mu-plugins')) {
            mkdir('/roots/app/public/content/mu-plugins', 0777, true);
        }

        // Create or update the Acorn boot mu-plugin
        $bootPlugin = <<<'PHP'
<?php
/*
Plugin Name: Acorn Boot
*/

use Roots\Acorn\Application;
use Roots\Acorn\Configuration\Exceptions;
use Roots\Acorn\Configuration\Middleware;

add_action('after_setup_theme', function () {
    Application::configure()
        ->withMiddleware(function (Middleware $middleware) {
            //
        })
        ->withExceptions(function (Exceptions $exceptions) {
            //
        })
        ->withRouting(
            web: '/roots/app/public/routes/web.php'
        )
        ->boot();
}, 0);
PHP;

        file_put_contents('/roots/app/public/content/mu-plugins/01-acorn-boot.php', $bootPlugin);
    }
}
