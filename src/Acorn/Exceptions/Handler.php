<?php

namespace Roots\Acorn\Exceptions;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Throwable;
use Whoops\Handler\HandlerInterface;
use Whoops\Run as Whoops;

use function Roots\app;

class Handler implements ExceptionHandlerContract
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the internal exception types that should not be reported.
     *
     * @var array
     */
    protected $internalDontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [];

    /**
     * Create a new exception handler instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \Exception
     */
    public function report(Throwable $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if (is_callable($reportCallable = [$e, 'report'])) {
            return $this->container->call($reportCallable);
        }

        try {
            $logger = app(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $logger->error(
            $e->getMessage(),
            array_merge(
                $this->exceptionContext($e),
                $this->context(),
                ['exception' => $e]
            )
        );
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    public function shouldReport(Throwable $e)
    {
        return ! $this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    protected function shouldntReport(Throwable $e)
    {
        $dontReport = array_merge($this->dontReport, $this->internalDontReport);

        return ! is_null(Arr::first($dontReport, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * Get the default exception context variables for logging.
     *
     * @param  \Throwable  $e
     * @return array
     */
    protected function exceptionContext(Throwable $e)
    {
        return [];
    }


    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    protected function context()
    {
        try {
            return array_filter([
                'userId' => get_current_user_id(),
            ]);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Render an exception into a response.
     *
     * @param  void        $request
     * @param  \Throwable  $e
     * @return string
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        try {
            return app()->environment('development') && class_exists(Whoops::class)
                        ? $this->renderExceptionWithWhoops($e)
                        : $this->renderExceptionWithSymfony($e, app()->environment('development'));
        } catch (Exception $e) {
            return $this->renderExceptionWithSymfony($e, app()->environment('development'));
        }
    }

    /**
     * Render an exception to a string using "Whoops".
     *
     * @param  \Throwable  $e
     * @return string
     */
    protected function renderExceptionWithWhoops(Throwable $e)
    {
        return tap(new Whoops(), function ($whoops) {
            $whoops->appendHandler($this->whoopsHandler());

            $whoops->allowQuit(false);
        })->handleException($e);
    }

    /**
     * Get the Whoops handler for the application.
     *
     * @return \Whoops\Handler\Handler
     */
    protected function whoopsHandler()
    {
        try {
            return app(HandlerInterface::class);
        } catch (BindingResolutionException $e) {
            return (new WhoopsHandler())->forDebug();
        }
    }

    /**
     * Render an exception to a string using Symfony.
     *
     * @param  \Throwable  $e
     * @param  bool  $debug
     * @return string
     */
    protected function renderExceptionWithSymfony(Throwable $e, $debug)
    {
        $renderer = new HtmlErrorRenderer($debug);

        return $renderer->render($e)->getAsString();
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Throwable  $e
     * @return void
     */
    public function renderForConsole($output, Throwable $e)
    {
        (new ConsoleApplication())->renderThrowable($e, $output);
    }
}
