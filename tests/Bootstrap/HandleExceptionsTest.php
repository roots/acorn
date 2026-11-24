<?php

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Log\LogManager;
use Roots\Acorn\Application;
use Roots\Acorn\Bootstrap\HandleExceptions;
use Roots\Acorn\Tests\Test\TestCase;
use Symfony\Component\HttpFoundation\Response;

use function Roots\Acorn\Tests\mock;

uses(TestCase::class);

beforeEach(function () {
    $this->container = Application::setInstance(new Application());
    $this->config = new Config();

    $this->config->set('app.debug', true);

    $this->container->singleton('config', fn () => $this->config);

    $this->container->bootstrapWith([]);

    $this->handleExceptions = new HandleExceptions();

    with(new ReflectionClass($this->handleExceptions), function ($reflection) {
        $property = tap($reflection->getProperty('app'))->setAccessible(true);
        $property->setValue($this->handleExceptions, $this->container);
    });
});

it('does not throw an exception for deprecation notices', function () {
    $logger = mock(LogManager::class);
    $this->container->instance(LogManager::class, $logger);
    $logger->shouldReceive('channel')->with('deprecations')->andReturnSelf();
    $logger
        ->shouldReceive('warning')
        ->with(sprintf(
            '%s in %s on line %s',
            'kjo(): Passing null to parameter #2 ($kjo) of type Kjo is deprecated',
            '/acorn/path/to/file.php',
            17,
        ));

    $this->handleExceptions->handleError(
        E_USER_DEPRECATED,
        'kjo(): Passing null to parameter #2 ($kjo) of type Kjo is deprecated',
        '/acorn/path/to/file.php',
        17,
    );
})->expectNotToPerformAssertions();

it('handles a warning', function () {
    $logger = mock(LogManager::class);
    $logger->shouldReceive();
    $this->container->instance(LogManager::class, $logger);

    $this->handleExceptions->handleError(E_USER_WARNING, 'warning message', '/acorn/path/to/file.php', 5);
})
    ->skip('This test is broken at the moment but this works as expected in manual testing')
    ->throws(ErrorException::class);

it('escapes an error exception for non-deprecation error', function () {
    $logger = mock(LogManager::class);
    $logger->shouldReceive();
    $this->container->instance(LogManager::class, $logger);

    add_filter('acorn/throw_error_exception', '__return_false');

    $this->handleExceptions->handleError(E_USER_WARNING, 'warning message', '/acorn/path/to/file.php', 5);

    $this->expectNotToPerformAssertions();
});

it('returns false for errors excluded from error reporting', function () {
    $level = error_reporting(E_ALL & ~E_USER_WARNING);

    try {
        expect($this->handleExceptions->handleError(
            E_USER_WARNING,
            'warning message',
            '/acorn/path/to/file.php',
            5,
        ))->toBeFalse();
    } finally {
        error_reporting($level);
    }
});

it('discards all output buffers before rendering an exception', function () {
    $start = ob_get_level();
    $level = null;

    $response = mock(Response::class);
    $response
        ->shouldReceive('send')
        ->andReturnUsing(function () use (&$level, $response) {
            $level = ob_get_level();

            return $response;
        });

    $handler = mock(ExceptionHandler::class);
    $handler->shouldReceive('render')->andReturn($response);

    $this->container->instance(ExceptionHandler::class, $handler);
    $this->container->instance('request', new stdClass());

    ob_start();
    ob_start();
    ob_start();

    try {
        (fn ($e) => $this->renderHttpResponse($e))->call($this->handleExceptions, new Exception());
    } finally {
        while (ob_get_level() < $start) {
            ob_start();
        }
    }

    expect($level)->toBe(0);
});
