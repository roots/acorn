<?php

namespace Roots\Acorn\Bootstrap;

use Throwable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as FoundationHandleExceptionsBootstrapper;

class HandleExceptions extends FoundationHandleExceptionsBootstrapper
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        self::$reservedMemory = str_repeat('x', 10240);

        $this->app = $app;

        if (
            ! $this->app->runningInConsole() &&
            is_readable(WP_CONTENT_DIR . '/fatal-error-handler.php')
        ) {
            return;
        }

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Roots\Acorn\Exceptions\Handler::class
        );

        parent::bootstrap($app);
    }

    /**
     * Render an exception as an HTTP response and send it.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderHttpResponse(Throwable $e)
    {
        $this->getExceptionHandler()->render('', $e);
    }
}
