<?php

namespace Roots\Acorn\Exceptions;

use Exception;
use Throwable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Exceptions\Handler as FoundationHandler;
use Whoops\Handler\HandlerInterface;
use Whoops\Run as Whoops;

use function Roots\app;

class Handler extends FoundationHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        try {
            return app()->environment(['development', 'local']) && class_exists(Whoops::class)
                        ? $this->renderExceptionWithWhoops($e)
                        : $this->renderExceptionWithSymfony($e, app()->environment('development'));
        } catch (Exception $e) {
            return $this->renderExceptionWithSymfony($e, app()->environment('development'));
        }
    }

    /**
     * Render an exception to a string using "Whoops".
     *
     * @param  Throwable  $e
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
}
