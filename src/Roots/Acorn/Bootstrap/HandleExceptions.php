<?php

namespace Roots\Acorn\Bootstrap;

use ErrorException;
use Throwable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as FoundationHandleExceptionsBootstrapper;
use Illuminate\Support\Arr;

class HandleExceptions extends FoundationHandleExceptionsBootstrapper
{
    /**
     * A list of the error types that are ignored.
     *
     * @var array
     */
    protected $ignoredErrors = ['E_USER_DEPRECATED'];

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

        if ($this->hasHandler() || ! $this->isDebug()) {
            return;
        }

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Roots\Acorn\Exceptions\Handler::class
        );
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param  int     $level
     * @param  string  $message
     * @param  string  $file
     * @param  int     $line
     * @param  array   $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (! (error_reporting() & $level)) {
            return;
        }

        if ($this->shouldIgnore($e = new ErrorException($message, 0, $level, $file, $line))) {
            return;
        }

        throw $e;
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

    /**
     * Determine if the error type should be ignored.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    public function shouldIgnore(Throwable $e): bool
    {
        return !$this->app->runningInConsole() && is_null(Arr::first($this->ignoredErrors, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * Determine if a fatal error handler drop-in exists.
     *
     * @return bool
     */
    protected function hasHandler(): bool
    {
        if ($this->app->runningInConsole()) {
            return false;
        }

        return is_readable(WP_CONTENT_DIR . '/fatal-error-handler.php');
    }

    /**
     * Determine if application debugging is enabled.
     *
     * @return bool
     */
    protected function isDebug(): bool
    {
        if ($this->app->runningInConsole()) {
            return false;
        }

        return (bool) $this->app->config->get('app.debug', WP_DEBUG);
    }
}
