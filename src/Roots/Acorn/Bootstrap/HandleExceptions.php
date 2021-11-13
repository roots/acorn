<?php

namespace Roots\Acorn\Bootstrap;

use Throwable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as FoundationHandleExceptionsBootstrapper;

use function apply_filters;

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

        if (!$this->isDebug() || $this->hasHandler()) {
            return;
        }

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Roots\Acorn\Exceptions\Handler::class
        );

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Report PHP deprecations, or convert PHP errors to ErrorException instances.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void|false
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        try {
            parent::handleError($level, $message, $file, $line, $context);
        } catch (Throwable $e) {
            if (! apply_filters('acorn/throw_error_exception', true, $e)) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Determine whether application debugging is enabled.
     *
     * @return bool
     */
    protected function isDebug()
    {
        return $this->app->config->get('app.debug', WP_DEBUG);
    }

    /**
     * Determine whether a fatal error handler drop-in exists.
     *
     * @return bool
     */
    protected function hasHandler()
    {
        return !$this->app->runningInConsole()
            && is_readable(WP_CONTENT_DIR . '/fatal-error-handler.php');
    }

    /**
     * Render an exception as an HTTP response and send it.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderHttpResponse(Throwable $e)
    {
        ob_end_clean();
        parent::renderHttpResponse($e);
    }
}
