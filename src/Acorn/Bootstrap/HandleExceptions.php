<?php

namespace Roots\Acorn\Bootstrap;

use ErrorException;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

class HandleExceptions
{
    /**
     * Reserved memory so that errors can be displayed properly on memory exhaustion.
     *
     * @var string
     */
    public static $reservedMemory;

    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

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

        error_reporting(-1 & ~E_USER_NOTICE);

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
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function handleException(Throwable $e)
    {
        try {
            self::$reservedMemory = null;

            $this->getExceptionHandler()->report($e);
        } catch (Exception $e) {
            //
        }

        if ($this->app->runningInConsole()) {
            $this->renderForConsole($e);
        } else {
            $this->renderHttpResponse($e);
        }
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Exception  $e
     * @return void
     */
    protected function renderForConsole(Exception $e)
    {
        $this->getExceptionHandler()->renderForConsole(new ConsoleOutput(), $e);
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
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalErrorFromPhpError($error, 0));
        }
    }

    /**
     * Create a new fatal error instance from an error array.
     *
     * @param  array  $error
     * @param  int|null  $traceOffset
     * @return \Symfony\Component\ErrorHandler\Error\FatalError
     */
    protected function fatalErrorFromPhpError(array $error, $traceOffset = null)
    {
        return new FatalError($error['message'], 0, $error, $traceOffset);
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

    /**
     * Get an instance of the exception handler.
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected function getExceptionHandler()
    {
        return $this->app->make(ExceptionHandler::class);
    }

    /**
     * Determine if a fatal error handler drop-in exists.
     *
     * @return bool
     */
    protected function hasHandler()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        return is_readable(WP_CONTENT_DIR . '/fatal-error-handler.php');
    }

    /**
     * Determine if application debugging is enabled.
     *
     * @return bool
     */
    protected function isDebug()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        return $this->app->config->get('app.debug', WP_DEBUG);
    }
}
