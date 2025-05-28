<?php

namespace Roots\Acorn\Application\Concerns;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;
use WP_CLI;

trait Bootable
{
    /**
     * The configuration used to boot the application.
     */
    protected array $bootConfiguration = [];

    /**
     * Boot the application and handle the request.
     */
    public function bootAcorn(array $bootConfiguration = []): static
    {
        if ($this->isBooted()) {
            return $this;
        }

        $this->bootConfiguration = $bootConfiguration;

        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        if ($this->runningInConsole()) {
            $this->enableHttpsInConsole();

            if (class_exists('WP_CLI')) {
                $this->bootWpCli();
            } elseif (defined('USING_ACORN_CLI') && USING_ACORN_CLI) {
                $this->bootConsole();
            }

            return $this;
        }

        $this->bootHttp();

        return $this;
    }

    /**
     * Boot the Application for console.
     */
    protected function bootConsole(): void
    {
        $kernel = $this->make(ConsoleKernelContract::class);

        $status = $kernel->handle(
            $input = new \Symfony\Component\Console\Input\ArgvInput,
            new \Symfony\Component\Console\Output\ConsoleOutput
        );

        $kernel->terminate($input, $status);

        exit($status);
    }

    /**
     * Boot the Application for WP-CLI.
     */
    protected function bootWpCli(): void
    {
        $kernel = $this->make(ConsoleKernelContract::class);
        $kernel->bootstrap();

        WP_CLI::add_command('acorn', function ($args, $options) use ($kernel) {
            $escaped = array_map(fn ($arg) => escapeshellarg($arg), $args);

            $command = implode(' ', $escaped);

            foreach ($options as $key => $value) {
                if ($key === 'interaction' && $value === false) {
                    $command .= ' --no-interaction';

                    continue;
                }

                $command .= " --{$key}";

                if ($value !== true) {
                    $command .= '='.escapeshellarg($value);
                }
            }

            $command = str_replace('\\', '\\\\', $command);

            $status = $kernel->handle(
                $input = new \Symfony\Component\Console\Input\StringInput($command),
                new \Symfony\Component\Console\Output\ConsoleOutput
            );

            $kernel->terminate($input, $status);

            WP_CLI::halt($status);
        });
    }

    /**
     * Boot the Application for HTTP requests.
     */
    protected function bootHttp(): void
    {
        $kernel = $this->make(HttpKernelContract::class);
        $request = Request::capture();

        $this->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $kernel->bootstrap($request);

        if ($this->app->handlesWordPressRequests()) {
            $this->registerWordPressRoute();
        }

        try {
            $route = $this->make('router')->getRoutes()->match($request);

            $this->registerRequestHandler($request, $route);
        } catch (Throwable) {
            //
        }
    }

    /**
     * Enable `$_SERVER[HTTPS]` in a console environment.
     */
    protected function enableHttpsInConsole(): void
    {
        $enable = apply_filters('acorn/enable_https_in_console', parse_url(get_option('home'), PHP_URL_SCHEME) === 'https');

        if ($enable) {
            $_SERVER['HTTPS'] = 'on';
        }
    }

    /**
     * Register a default route for WordPress requests.
     */
    protected function registerWordPressRoute(): void
    {
        Route::any('{any?}', fn () => tap(response(''), function (Response $response) {
            foreach (headers_list() as $header) {
                [$header, $value] = preg_split("/:\s{0,1}/", $header, 2);

                if (! headers_sent()) {
                    header_remove($header);
                }

                $response->header($header, $value, $header !== 'Set-Cookie');
            }

            $response->setStatusCode(http_response_code());

            $content = '';

            $levels = ob_get_level();

            for ($i = 0; $i < $levels; $i++) {
                $content .= ob_get_clean();
            }

            $response->setContent($content);
        }))
            ->middleware('wordpress')
            ->where('any', '.*')
            ->name('wordpress');
    }

    /**
     * Register the request handler.
     */
    protected function registerRequestHandler(
        \Illuminate\Http\Request $request,
        ?\Illuminate\Routing\Route $route
    ): void {
        $path = Str::finish($request->getBaseUrl(), $request->getPathInfo());

        $except = collect([
            admin_url(),
            wp_login_url(),
            wp_registration_url(),
            rest_url(),
        ])->map(fn ($url) => parse_url($url, PHP_URL_PATH))->unique()->filter();

        if (
            Str::startsWith($path, $except->all()) ||
            Str::endsWith($path, '.php')
        ) {
            return;
        }

        add_filter('do_parse_request', function ($condition, $wp, $params) use ($route) {
            if (! $route) {
                return $condition;
            }

            return apply_filters('acorn/router/do_parse_request', $condition, $wp, $params);
        }, 100, 3);

        if ($route->getName() !== 'wordpress') {
            add_action('parse_request', fn () => $this->handleRequest($request));

            return;
        }

        if (
            ! $this->app->handlesWordPressRequests() ||
            redirect_canonical(null, false)
        ) {
            return;
        }

        ob_start();

        remove_action('shutdown', 'wp_ob_end_flush_all', 1);

        $kernel = $this->make(HttpKernelContract::class);

        $response = $kernel->handle($request);

        $response->headers->remove('cache-control');

        add_action('send_headers', fn () => $response->sendHeaders(), 100);

        add_action('shutdown', function () use ($kernel, $request, $response) {
            $response->sendContent();

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } elseif (function_exists('litespeed_finish_request')) {
                litespeed_finish_request();
            } elseif (! in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
                Response::closeOutputBuffers(0, true);
                flush();
            }

            $kernel->terminate($request, $response);

            exit((int) $response->isServerError());
        }, 100);
    }

    /**
     * Handle the request.
     */
    public function handleRequest(Request $request): void
    {
        $kernel = $this->make(HttpKernelContract::class);

        $response = $kernel->handle($request);

        $response->send();

        $kernel->terminate($request, $response);

        exit((int) $response->isServerError());
    }

    /**
     * Retrieve the boot configuration.
     */
    public function getBootConfiguration(): array
    {
        return $this->bootConfiguration;
    }
}
