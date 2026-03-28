<?php

use Roots\Acorn\Filesystem\Filesystem;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

class TestableFilesystem extends Filesystem
{
    public function isWithinOpenBasedir($path, $openBasedir = null)
    {
        return parent::isWithinOpenBasedir($path, $openBasedir);
    }
}

it('should support basic globs', function () {
    expect((new Filesystem())->glob($this->fixture('closest/*.txt')))->toEqual([$this->fixture('closest/kjo.txt')]);
});

it('should normalize paths with multiple separators', function () {
    expect((new Filesystem())->normalizePath('/a//b///c/d//e'))->toEqual('/a/b/c/d/e');
});

it('should normalize paths separated by backslashes', function () {
    expect((new Filesystem())->normalizePath('/a\\\\b\\\\c/d//e'))->toEqual('/a/b/c/d/e');
});

it('should normalize paths to arbitrary separator', function () {
    expect((new Filesystem())->normalizePath('/a\\\\b\\\\c/d//e', '|'))->toEqual('|a|b|c|d|e');
});

it('should find the closest path within the filesystem', function () {
    expect((new Filesystem())->closest($this->fixture('closest/a/b'), 'kjo.txt'))
        ->toEqual($this->fixture('closest/kjo.txt'));

    expect((new Filesystem())->closest($this->fixture('closest/a/b'), 'bdubs.txt'))
        ->toEqual($this->fixture('closest/a/b/bdubs.txt'));

    expect((new Filesystem())->closest($this->fixture('closest/a/b'), 'apray.txt'))->toBeNull();
});

it('should check open_basedir boundaries correctly', function () {
    $fs = new TestableFilesystem();
    $restriction = '/home/user' . PATH_SEPARATOR . '/tmp';

    // No restriction — everything is allowed
    expect($fs->isWithinOpenBasedir('/any/path', ''))->toBeTrue();

    // Path within allowed directory
    expect($fs->isWithinOpenBasedir('/home/user/project', $restriction))->toBeTrue();
    expect($fs->isWithinOpenBasedir('/tmp', $restriction))->toBeTrue();
    expect($fs->isWithinOpenBasedir('/tmp/subdir', $restriction))->toBeTrue();

    // Sibling path that shares a prefix — must NOT match
    expect($fs->isWithinOpenBasedir('/home/user2', $restriction))->toBeFalse();
    expect($fs->isWithinOpenBasedir('/home/user2/project', $restriction))->toBeFalse();

    // Path completely outside allowed directories
    expect($fs->isWithinOpenBasedir('/var/www', $restriction))->toBeFalse();
    expect($fs->isWithinOpenBasedir('/home', $restriction))->toBeFalse();

    // Empty entries in restriction should be ignored
    expect($fs->isWithinOpenBasedir('/var/www', '/home/user' . PATH_SEPARATOR . PATH_SEPARATOR . '/tmp'))->toBeFalse();
});

it('should determine the relative path between two absolute paths', function () {
    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/', '/dir_a/dir_b/dir_c/file_d'))->toEqual('dir_c/file_d');

    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/dir_c/dir_d/', '/dir_a/dir_b/'))->toEqual('../../');

    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/', '/dir_a/file_kjo'))->toEqual('../file_kjo');

    expect((new Filesystem())->getRelativePath('/dir_a/dir_b/', '/dir_a/dir_b/'))->toEqual('');
});
