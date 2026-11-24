<?php

use Roots\Acorn\Application;
use Roots\Acorn\Tests\Test\TestCase;
use Roots\Acorn\View\ViewServiceProvider;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

it('resolves the relative hash path', function (string $base, ?string $content, string $expected) {
    $root = realpath(temp('relative_hash'));

    foreach (array_filter([$base, $content]) as $path) {
        is_dir("{$root}/{$path}") || mkdir("{$root}/{$path}", 0o777, true);
    }

    $provider = new class(new Application("{$root}/{$base}")) extends ViewServiceProvider {
        public ?string $content = null;

        protected function contentPath(): ?string
        {
            return $this->content;
        }

        public function path(): string
        {
            return $this->relativeHashPath();
        }
    };

    $provider->content = $content ? "{$root}/{$content}/" : null;

    expect($provider->path())->toBe("{$root}/{$expected}");
})->with([
    'theme inside the content directory' => ['bedrock/web/app/themes/sage', 'bedrock/web/app', 'bedrock/web/app'],
    'content directory inside the base' => ['radicle', 'radicle/public/content', 'radicle'],
    'base outside the content directory' => ['deploy/sage', 'site/wp-content', 'deploy/sage'],
    'base with a similar prefix' => ['site/web/app-themes/sage', 'site/web/app', 'site/web/app-themes/sage'],
    'no content directory' => ['site/theme', null, 'site/theme'],
]);
