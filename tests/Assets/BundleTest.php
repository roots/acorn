<?php

use Illuminate\Support\Collection;
use Roots\Acorn\Assets\Bundle;
use Roots\Acorn\Tests\TestCase;

use function Brain\Monkey\Functions\expect as expectGlobal;
use function Brain\Monkey\Functions\stubs;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;
use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

beforeEach(fn () => Bundle::resetInlinedSources());

it('can get styles and scripts collections', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    expect($app->js())->toBeInstanceOf(Collection::class);
    expect($app->css())->toBeInstanceOf(Collection::class);

    assertMatchesJsonSnapshot($app->js()->toJson());
    assertMatchesJsonSnapshot($app->css()->toJson());
});

it('accepts a callback for styles and scripts', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    $app->js(fn ($handle, $src, $dependencies) =>
        assertMatchesSnapshot(compact('handle', 'src', 'dependencies'))
    );

    $app->css(fn ($handle, $src) =>
        assertMatchesSnapshot(compact('handle', 'src'))
    );
});

it('can enqueue css', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    stubs([
        'wp_enqueue_style' => fn (...$args) => assertMatchesSnapshot($args),
    ]);

    $app->enqueueCss();
});

it('can enqueue js', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    stubs([
        'wp_enqueue_script' => fn (...$args) => assertMatchesSnapshot($args),
    ]);

    expectGlobal('wp_add_inline_script')
        ->zeroOrMoreTimes()
        ->withAnyArgs();

    $app->enqueueJs();
});

it('can inline a single runtime', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    stubs([
        'wp_add_inline_script' => fn (...$args) => assertMatchesSnapshot($args),
    ]);

    expectGlobal('wp_enqueue_script')
        ->zeroOrMoreTimes()
        ->withAnyArgs();

    $app->enqueueJs();
});

it('can inline multiple runtimes', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_multi_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_multi_runtime'), 'https://k.jo');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_multi_runtime'), 'https://k.jo');

    expectGlobal('wp_add_inline_script')
        ->twice()
        ->withAnyArgs();

    expectGlobal('wp_enqueue_script')
        ->twice()
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});

it('does not inline duplicate single runtimes', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    expectGlobal('wp_add_inline_script')
        ->once()
        ->withAnyArgs();

    expectGlobal('wp_enqueue_script')
        ->twice()
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});
