<?php

use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\Tests\TestCase;

use function Roots\Acorn\Tests\fixture;

uses(TestCase::class);

it('should normalize paths with multiple separators', function () {
    expect((new Filesystem())->normalizePath('/a//b///c/d//e'))
        ->toEqual('/a/b/c/d/e');
});

it('should normalize paths separated by backslashes', function () {
    expect((new Filesystem())->normalizePath('/a\\\\b\\\\c/d//e'))
        ->toEqual('/a/b/c/d/e');
});

it('should normalize paths to arbitrary separator', function () {
    expect((new Filesystem())->normalizePath('/a\\\\b\\\\c/d//e', '|'))
        ->toEqual('|a|b|c|d|e');
});

it('should find the closest path within the filesystem', function () {
    expect((new Filesystem())->closest($this->fixture('closest/a/b'), 'kjo.txt'))
        ->toEqual($this->fixture('closest/kjo.txt'));

    expect((new Filesystem())->closest($this->fixture('closest/a/b'), 'bdubs.txt'))
        ->toEqual($this->fixture('closest/a/b/bdubs.txt'));

    expect((new Filesystem())->closest($this->fixture('closest/a/b'), 'apray.txt'))
        ->toBeNull();
});

it('should determine the relative path between two absolute paths', function () {
    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/', '/dir_a/dir_b/dir_c/file_d'))
        ->toEqual('dir_c/file_d');

    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/dir_c/dir_d/', '/dir_a/dir_b/'))
        ->toEqual('../../');

    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/', '/dir_a/file_kjo'))
        ->toEqual('../file_kjo');

    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/', '/dir_a/dir_b/'))
        ->toEqual('');
});
