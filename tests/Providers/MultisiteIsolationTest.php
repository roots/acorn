<?php

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Queue;
use Roots\Acorn\Providers\AcornServiceProvider;
use Roots\Acorn\Tests\Test\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->stub('is_multisite', fn () => $this->isMultisite);
    $this->stub('get_current_blog_id', fn () => $this->blogId);
    $this->stub('switch_to_blog', function ($id) {
        $this->blogIdStack[] = $this->blogId;
        $this->blogId = $id;
        do_action('switch_blog');
    });
    $this->stub('restore_current_blog', function () {
        $this->blogId = $this->blogIdStack ? array_pop($this->blogIdStack) : 1;
        do_action('switch_blog');
    });

    $this->isMultisite = false;
    $this->blogId = 1;
    $this->blogIdStack = [];
});

afterEach(function () {
    Queue::createPayloadUsing(null);
});

function invokeConfigureMultisite(AcornServiceProvider $provider): void
{
    $method = new ReflectionMethod($provider, 'configureMultisite');
    $method->invoke($provider);
}

function createProviderWithConfig(array $config = []): array
{
    $container = new Container;

    $container->instance('config', new Repository(array_merge([
        'cache' => ['prefix' => 'acorn_cache_'],
        'session' => ['cookie' => 'acorn_session'],
    ], $config)));

    $container->instance('events', new Dispatcher($container));

    $provider = new AcornServiceProvider($container);

    return [$provider, $container];
}

it('should prefix cache keys per blog on multisite', function () {
    $this->isMultisite = true;
    $this->blogId = 3;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('cache.prefix'))
        ->toBe('acorn_cache_blog_3_');
});

it('should set unique session cookie per blog on multisite', function () {
    $this->isMultisite = true;
    $this->blogId = 5;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('session.cookie'))
        ->toBe('acorn_session_5');
});

it('should not modify config on single-site', function () {
    $this->isMultisite = false;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('cache.prefix'))->toBe('acorn_cache_');
    expect($container->make('config')->get('session.cookie'))->toBe('acorn_session');
});

it('should isolate cache prefix between blogs', function () {
    $this->isMultisite = true;

    $this->blogId = 1;
    [$provider1, $container1] = createProviderWithConfig();
    invokeConfigureMultisite($provider1);

    $this->blogId = 2;
    [$provider2, $container2] = createProviderWithConfig();
    invokeConfigureMultisite($provider2);

    $prefix1 = $container1->make('config')->get('cache.prefix');
    $prefix2 = $container2->make('config')->get('cache.prefix');

    expect($prefix1)->not->toBe($prefix2);
    expect($prefix1)->toBe('acorn_cache_blog_1_');
    expect($prefix2)->toBe('acorn_cache_blog_2_');
});

it('should update config when blog is switched', function () {
    $this->isMultisite = true;
    $this->blogId = 1;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    expect($container->make('config')->get('cache.prefix'))->toBe('acorn_cache_blog_1_');
    expect($container->make('config')->get('session.cookie'))->toBe('acorn_session_1');

    // Simulate switch_to_blog(4)
    $this->blogId = 4;
    do_action('switch_blog');

    expect($container->make('config')->get('cache.prefix'))->toBe('acorn_cache_blog_4_');
    expect($container->make('config')->get('session.cookie'))->toBe('acorn_session_4');
});

it('should inject blogId into queue job payloads', function () {
    $this->isMultisite = true;
    $this->blogId = 3;

    [$provider, $container] = createProviderWithConfig();
    invokeConfigureMultisite($provider);

    $reflection = new ReflectionClass(Queue::class);
    $prop = $reflection->getProperty('createPayloadCallbacks');
    $callbacks = $prop->getValue();

    expect($callbacks)->not->toBeEmpty();

    $result = call_user_func(end($callbacks), 'sync', 'default', []);
    expect($result)->toBe(['blogId' => 3]);
});

it('should switch blog context when processing a queued job', function () {
    $this->isMultisite = true;
    $this->blogId = 1;

    [$provider, $container] = createProviderWithConfig();
    $container->instance('events', new Dispatcher($container));
    invokeConfigureMultisite($provider);

    $mockJob = new class
    {
        public function payload()
        {
            return ['blogId' => 2];
        }
    };

    expect($this->blogId)->toBe(1);

    $container->make('events')->dispatch(new JobProcessing('sync', $mockJob));
    expect($this->blogId)->toBe(2);

    $container->make('events')->dispatch(new JobProcessed('sync', $mockJob));
    expect($this->blogId)->toBe(1);
});

it('should restore blog context after job exception', function () {
    $this->isMultisite = true;
    $this->blogId = 1;

    [$provider, $container] = createProviderWithConfig();
    $container->instance('events', new Dispatcher($container));
    invokeConfigureMultisite($provider);

    $mockJob = new class
    {
        public function payload()
        {
            return ['blogId' => 5];
        }
    };

    $container->make('events')->dispatch(new JobProcessing('sync', $mockJob));
    expect($this->blogId)->toBe(5);

    $container->make('events')->dispatch(new JobExceptionOccurred('sync', $mockJob, new Exception('test')));
    expect($this->blogId)->toBe(1);
});

it('should not switch blog for jobs without blogId', function () {
    $this->isMultisite = true;
    $this->blogId = 1;

    [$provider, $container] = createProviderWithConfig();
    $container->instance('events', new Dispatcher($container));
    invokeConfigureMultisite($provider);

    // Simulate an outer switch_to_blog(3) that happened before the job
    switch_to_blog(3);
    expect($this->blogId)->toBe(3);
    expect($this->blogIdStack)->toBe([1]);

    $mockJob = new class
    {
        public function payload()
        {
            return [];
        }
    };

    // Process a legacy job without blogId — should not touch the stack
    $container->make('events')->dispatch(new JobProcessing('sync', $mockJob));
    expect($this->blogId)->toBe(3);
    expect($this->blogIdStack)->toBe([1]);

    $container->make('events')->dispatch(new JobProcessed('sync', $mockJob));
    expect($this->blogId)->toBe(3);
    expect($this->blogIdStack)->toBe([1]);

    // The outer context should still be restorable
    restore_current_blog();
    expect($this->blogId)->toBe(1);
});

it('should handle nested job processing correctly', function () {
    $this->isMultisite = true;
    $this->blogId = 1;

    [$provider, $container] = createProviderWithConfig();
    $container->instance('events', new Dispatcher($container));
    invokeConfigureMultisite($provider);

    $outerJob = new class
    {
        public function payload()
        {
            return ['blogId' => 2];
        }
    };

    $innerJob = new class
    {
        public function payload()
        {
            return ['blogId' => 3];
        }
    };

    // Outer job starts — switches to blog 2
    $container->make('events')->dispatch(new JobProcessing('sync', $outerJob));
    expect($this->blogId)->toBe(2);

    // Inner job starts — switches to blog 3
    $container->make('events')->dispatch(new JobProcessing('sync', $innerJob));
    expect($this->blogId)->toBe(3);

    // Inner job finishes — restores to blog 2
    $container->make('events')->dispatch(new JobProcessed('sync', $innerJob));
    expect($this->blogId)->toBe(2);

    // Outer job finishes — restores to blog 1
    $container->make('events')->dispatch(new JobProcessed('sync', $outerJob));
    expect($this->blogId)->toBe(1);
});

it('should not consume outer restore when inner job has no blogId', function () {
    $this->isMultisite = true;
    $this->blogId = 1;

    [$provider, $container] = createProviderWithConfig();
    $container->instance('events', new Dispatcher($container));
    invokeConfigureMultisite($provider);

    $outerJob = new class
    {
        public function payload()
        {
            return ['blogId' => 2];
        }
    };

    $innerJob = new class
    {
        public function payload()
        {
            return [];
        }
    };

    // Outer job starts — switches to blog 2
    $container->make('events')->dispatch(new JobProcessing('sync', $outerJob));
    expect($this->blogId)->toBe(2);

    // Inner job without blogId starts — no switch
    $container->make('events')->dispatch(new JobProcessing('sync', $innerJob));
    expect($this->blogId)->toBe(2);

    // Inner job finishes — should NOT restore since it never switched
    $container->make('events')->dispatch(new JobProcessed('sync', $innerJob));
    expect($this->blogId)->toBe(2);

    // Outer job finishes — restores to blog 1
    $container->make('events')->dispatch(new JobProcessed('sync', $outerJob));
    expect($this->blogId)->toBe(1);
});
