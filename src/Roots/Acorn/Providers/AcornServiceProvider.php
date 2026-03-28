<?php

namespace Roots\Acorn\Providers;

use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Queue;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Roots\Acorn\Assets\AssetsServiceProvider;
use Roots\Acorn\Filesystem\Filesystem;

class AcornServiceProvider extends ServiceProvider
{
    /**
     * Core configs.
     *
     * @var string[]
     */
    protected $configs = ['app', 'services'];

    /**
     * Provider configs.
     *
     * @var string[]
     */
    protected $providerConfigs = [
        AuthServiceProvider::class => 'auth',
        BroadcastServiceProvider::class => 'broadcasting',
        CacheServiceProvider::class => 'cache',
        DatabaseServiceProvider::class => 'database',
        FilesystemServiceProvider::class => 'filesystems',
        HashServiceProvider::class => 'hashing',
        LogServiceProvider::class => 'logging',
        MailServiceProvider::class => 'mail',
        QueueServiceProvider::class => 'queue',
        SessionServiceProvider::class => 'session',
        ViewServiceProvider::class => 'view',
        AssetsServiceProvider::class => 'assets',
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishables();
            $this->registerPostInitEvent();
        }

        $this->configureMultisite();
        $this->poweredBy();
    }

    /**
     * Add a header.
     *
     * Disable with `add_filter('acorn/powered_by', '__return_false');`
     *
     * @return void
     */
    protected function poweredBy()
    {
        add_filter('wp_headers', function ($headers) {
            if (! apply_filters('acorn/powered_by', true)) {
                return $headers;
            }

            $headers['X-Powered-By'] = $this->app->version();

            return $headers;
        });
    }

    /**
     * Publish application files.
     *
     * @return void
     */
    protected function registerPublishables()
    {
        $this->publishConfigs();
    }

    /**
     * Publish application configs.
     *
     * @return void
     */
    protected function publishConfigs()
    {
        foreach ($this->filterPublishableConfigs() as $config) {
            $path = dirname(__DIR__, 4);

            $file = file_exists($stub = "{$path}/config-stubs/{$config}.php") ? $stub : "{$path}/config/{$config}.php";

            $this->publishes([
                $file => config_path("{$config}.php"),
            ], ['acorn', 'acorn-configs']);
        }
    }

    /**
     * Filters out providers that aren't registered
     *
     * @return string[]
     */
    protected function filterPublishableConfigs()
    {
        $configs = array_filter(
            $this->providerConfigs,
            fn ($provider) => class_exists($provider) && $this->app->getProviders($provider),
            ARRAY_FILTER_USE_KEY,
        );

        return array_unique(array_merge($this->configs, array_values($configs)));
    }

    /**
     * Configure cache, session, and queue isolation for multisite.
     *
     * @return void
     */
    protected function configureMultisite()
    {
        if (! function_exists('is_multisite') || ! is_multisite()) {
            return;
        }

        $config = $this->app->make('config');
        $basePrefix = $config->get('cache.prefix', '');
        $baseCookie = $config->get('session.cookie', 'wordpress_session');

        $applyBlogConfig = function () use ($config, $basePrefix, $baseCookie) {
            $blogId = get_current_blog_id();
            $config->set('cache.prefix', "{$basePrefix}blog_{$blogId}_");
            $config->set('session.cookie', "{$baseCookie}_{$blogId}");
        };

        $applyBlogConfig();

        add_action('switch_blog', $applyBlogConfig);

        $this->configureMultisiteQueue();
    }

    /**
     * Configure queue isolation for multisite.
     *
     * Injects the current blog ID into every queued job payload and
     * switches to the correct blog context before the job is processed.
     *
     * @return void
     */
    protected function configureMultisiteQueue()
    {
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ['blogId' => get_current_blog_id()];
        });

        $events = $this->app->make('events');
        $switchedJobs = [];

        $events->listen(JobProcessing::class, function (JobProcessing $event) use (&$switchedJobs) {
            $payload = $event->job->payload();

            if (isset($payload['blogId'])) {
                switch_to_blog((int) $payload['blogId']);
                $switchedJobs[spl_object_id($event->job)] = true;
            }
        });

        $restore = function ($event) use (&$switchedJobs) {
            $id = spl_object_id($event->job);

            if (isset($switchedJobs[$id])) {
                restore_current_blog();
                unset($switchedJobs[$id]);
            }
        };

        $events->listen(JobProcessed::class, $restore);
        $events->listen(JobExceptionOccurred::class, $restore);
    }

    /**
     * Remove zeroconf storage directory after running acorn:init.
     *
     * @return void
     */
    protected function registerPostInitEvent()
    {
        $this->app
            ->make('events')
            ->listen(function (CommandFinished $event) {
                if ($event->command !== 'acorn:init') {
                    return;
                }

                if (! is_dir(base_path('storage'))) {
                    return;
                }

                $files = new Filesystem();

                $files->deleteDirectory(WP_CONTENT_DIR . '/cache/acorn');
            });
    }
}
