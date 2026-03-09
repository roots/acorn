<?php

use Roots\Acorn\Exceptions\SkipProviderException;

it('should inherit file and line from previous exception', function () {
    $previous = new RuntimeException('Something broke');

    $exception = new SkipProviderException('Skipping provider', 0, $previous);

    expect($exception->getFile())->toBe($previous->getFile());
    expect($exception->getLine())->toBe($previous->getLine());
});

it('should use own file and line when no previous exception', function () {
    $exception = new SkipProviderException('Skipping provider');

    expect($exception->getFile())->toBeString()->not->toBeEmpty();
    expect($exception->getLine())->toBeGreaterThan(0);
});
