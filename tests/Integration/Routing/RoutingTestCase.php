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

    private $originalFunctionsContent;
    private $functionsFile = '/roots/app/public/content/themes/sage/functions.php';
    private $routesFile = '/roots/app/public/content/themes/sage/routes/web.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearStubs();

        // Ensure Sage routes directory exists
        $routesDir = dirname($this->routesFile);
        if (! is_dir($routesDir)) {
            mkdir($routesDir, 0777, true);
        }

        // Create test routes file
        $webRoutes = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', fn() => 'Howdy')->name('test');
PHP;

        file_put_contents($this->routesFile, $webRoutes);

        // Backup original functions.php and add routing
        $this->originalFunctionsContent = file_get_contents($this->functionsFile);

        if (!str_contains($this->originalFunctionsContent, 'withRouting')) {
            $newContent = str_replace(
                '->boot();',
                '->withRouting(web: __DIR__ . \'/routes/web.php\')' . "\n    ->boot();",
                $this->originalFunctionsContent
            );
            file_put_contents($this->functionsFile, $newContent);
        }

        // Ensure Sage is the active theme
        if (function_exists('switch_theme')) {
            switch_theme('sage');
        }
    }

    protected function tearDown(): void
    {
        // Restore original functions.php
        if ($this->originalFunctionsContent) {
            file_put_contents($this->functionsFile, $this->originalFunctionsContent);
        }

        // Clean up test routes file
        if (file_exists($this->routesFile)) {
            unlink($this->routesFile);
        }

        parent::tearDown();
    }
}
