<?php

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Roots\Acorn\Bootstrap\HandleExceptions;
use Roots\Acorn\Tests\Test\TestCase;

use function Roots\Acorn\Tests\mock;

uses(TestCase::class);


beforeAll(function () {
    if (! defined('WP_DEBUG')) {
        define('WP_DEBUG', true);
    }
});

beforeEach(function () {
    /** @var \Mockery\MockInterface|ApplicationContract */
    $this->application = mock(ApplicationContract::class);
    $this->application
        ->shouldIgnoreMissing()
        ->shouldreceive('runningInConsole')
        ->andReturn(false);
    $this->application->config = new Config(['app' => ['debug' => true]]);
    $this->handleExceptions = new HandleExceptions();
});

it('registers error handler', function () {
    set_error_handler(null);

    $this->handleExceptions->bootstrap($this->application);

    expect(set_error_handler(null))->not()->toBeNull();
});

it('registers exception handler', function () {
    set_exception_handler(null);

    $this->handleExceptions->bootstrap($this->application);

    expect(set_exception_handler(null))->not()->toBeNull();
});

it('does not register handlers when debugging is disabled', function () {
    $this->application->config->set('app.debug', false);

    set_error_handler(null);
    set_exception_handler(null);

    $this->handleExceptions->bootstrap($this->application);

    expect(set_error_handler(null))->toBeNull();
    expect(set_exception_handler(null))->toBeNull();
});
