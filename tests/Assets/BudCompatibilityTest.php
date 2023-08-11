<?php

use Illuminate\Support\Collection;
use Roots\Acorn\Assets\Bundle;
use Roots\Acorn\Tests\Test\TestCase;

use function Spatie\Snapshots\assertMatchesJsonSnapshot;
use function Spatie\Snapshots\assertMatchesSnapshot;

uses(TestCase::class);

beforeEach(fn () => Bundle::resetInlinedSources());

it('supports bud v6: build', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_v6_single_runtime/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_v6_single_runtime/public'), 'https://k.jo/public');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_v6_single_runtime/public'), 'https://k.jo/public');

    $this->stub('wp_add_inline_script')
        ->shouldBeCalled()
        ->once()
        ->withAnyArgs();

    $this->stub('wp_enqueue_script')
        ->shouldBeCalled()
        ->times(4)
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});

it('supports bud v6: build --esm', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_v6_single_runtime_esm/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_v6_single_runtime_esm/public'), 'https://k.jo/public');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_v6_single_runtime_esm/public'), 'https://k.jo/public');

    $this->stub('wp_add_inline_script')
        ->shouldBeCalled()
        ->once()
        ->withAnyArgs();

    $this->stub('wp_enqueue_script')
        ->shouldBeCalled()
        ->times(4)
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});

it('supports bud v6: dev', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_v6_single_runtime_hmr/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_v6_single_runtime_hmr/public'), 'https://k.jo/public');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_v6_single_runtime_hmr/public'), 'https://k.jo/public');

    $this->stub('wp_add_inline_script')
        ->shouldBeCalled()
        ->never();

    $this->stub('wp_enqueue_script')
        ->shouldBeCalled()
        ->times(2)
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});

it('supports bud v6: dev --esm', function () {
    $manifest = json_decode(file_get_contents($this->fixture('bud_v6_single_runtime_hmr_esm/public/entrypoints.json')), JSON_OBJECT_AS_ARRAY);
    $app = new Bundle('app', $manifest['app'], $this->fixture('bud_v6_single_runtime_hmr_esm/public'), 'https://k.jo/public');
    $editor = new Bundle('editor', $manifest['editor'], $this->fixture('bud_v6_single_runtime_hmr_esm/public'), 'https://k.jo/public');

    $this->stub('wp_add_inline_script')
        ->shouldBeCalled()
        ->never();

    $this->stub('wp_enqueue_script')
        ->shouldBeCalled()
        ->times(2)
        ->withAnyArgs();

    $app->enqueueJs();
    $editor->enqueueJs();
});
