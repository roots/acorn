<?php

use Illuminate\Support\Collection;
use Roots\Acorn\Assets\Bundle;
use Roots\Acorn\Tests\Test\TestCase;

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

    $this->stub('wp_enqueue_style', fn (...$args) => assertMatchesSnapshot($args));

    $app->enqueueCss();
});

it('can dequeue css', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    $this->stub('wp_enqueue_style')->shouldBeCalled();
    $this->stub('wp_dequeue_style', fn (...$args) => assertMatchesSnapshot($args));

    $app->enqueueCss()->dequeueCss();
});

it('can silently fail to enqueue css', function () {
    $stub = $this->stub('wp_enqueue_style');
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime_dev/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime_dev'), 'https://k.jo');

    $app->enqueueCss();

    $stub->shouldNotHaveBeenCalled();
});

it('can enqueue js', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    $this->stub('wp_enqueue_script', fn (...$args) => assertMatchesSnapshot($args));
    $this->stub('wp_add_inline_script')->shouldBeCalled();

    $app->enqueueJs();
});

it('can dequeue js', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    $this->stub('wp_enqueue_script')->shouldBeCalled();
    $this->stub('wp_add_inline_script')->shouldBeCalled();

    $this->stub('wp_dequeue_script', fn (...$args) => assertMatchesSnapshot($args));

    $app->enqueueJs()->dequeueJs();
});

it('can inline a single runtime', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    $this->stubs([
        'wp_add_inline_script' => fn (...$args) => assertMatchesSnapshot($args),
        'wp_enqueue_script',
    ]);

    $this->stub('wp_enqueue_script')
        ->shouldBeCalled()
        ->zeroOrMoreTimes()
        ->withAnyArgs();

    $app->enqueueJs();
});

it('can inline multiple runtimes', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_multi_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_multi_runtime'), 'https://k.jo');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_multi_runtime'), 'https://k.jo');

    $this->stub('wp_add_inline_script')
        ->shouldBeCalled()
        ->twice()
        ->withAnyArgs();

    $this->stub('wp_enqueue_script')
        ->shouldBeCalled()
        ->twice()
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});

it('does not inline duplicate single runtimes', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    $this->stub('wp_add_inline_script')
        ->shouldBeCalled()
        ->once()
        ->withAnyArgs();

    $this->stub('wp_enqueue_script')
        ->shouldBeCalled()
        ->twice()
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});

it('can conditionally get assets', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_single_runtime'), 'https://k.jo');

    assertMatchesJsonSnapshot($app->js()->toJson());

    expect($app->when(false)->js()->toArray())->toBeEmpty();
    expect($app->when(true)->js()->toArray())->not()->toBeEmpty();

    expect($app->when(fn () => false)->js()->toArray())->toBeEmpty();
    expect($app->when(fn () => true)->js()->toArray())->not()->toBeEmpty();

    expect($app->when('min', 0, 1)->js()->toArray())->toBeEmpty();
    expect($app->when('max', 0, 1)->js()->toArray())->not()->toBeEmpty();
});
