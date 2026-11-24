<?php

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\View;
use Roots\Acorn\Application;
use Roots\Acorn\Tests\Test\TestCase;
use Roots\Acorn\View\Composer;
use Roots\Acorn\View\Composers\Concerns\Cacheable;

use function Roots\Acorn\Tests\temp;

uses(TestCase::class);

it('forgets the cached composer data', function () {
    $this->stub('get_the_ID', fn () => 1);
    $this->stub('get_post_type', fn () => 'post');
    $this->stub('get_queried_object', fn () => null);

    $app = new Application(temp('cacheable'));
    $app->instance('config', new Repository([
        'cache' => [
            'default' => 'file',
            'stores' => ['file' => ['driver' => 'file', 'path' => temp('cacheable/' . uniqid())]],
        ],
    ]));
    $app->instance('files', new Filesystem());
    $app->singleton('cache', fn ($app) => new CacheManager($app));
    Facade::setFacadeApplication($app);

    $composer = new class extends Composer {
        use Cacheable;

        public $generation = 1;

        public function with()
        {
            return ['generation' => $this->generation];
        }

        public function clear()
        {
            $this->forget();
        }
    };

    $render = function () use ($composer) {
        $rendered = [];

        $view = Mockery::mock(View::class);
        $view->shouldReceive('getData')->andReturn([]);
        $view->shouldReceive('with')->andReturnUsing(function ($data) use (&$rendered, $view) {
            $rendered = $data;

            return $view;
        });

        $composer->compose($view);

        return $rendered['generation'];
    };

    $render();
    $composer->generation = 2;

    expect($render())->toBe(1);

    $composer->clear();

    expect($render())->toBe(2);
});
