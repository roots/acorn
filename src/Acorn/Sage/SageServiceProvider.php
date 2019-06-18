<?php

namespace Roots\Acorn\Sage;

use function Roots\add_filters;

use Roots\Acorn\Config;
use Roots\Acorn\Sage\Sage;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\ServiceProvider;

class SageServiceProvider extends ServiceProvider
{
    /** {@inheritDoc} */
    public function register()
    {
        $this->app->singleton('sage', Sage::class);
        $this->app->bind('sage.finder', ViewFinder::class);
    }

    public function boot()
    {
        if ($this->app->bound('view')) {
            $this->app['sage']->attach();
        }
    }

    public function preflight(\WP_Http $client)
    {
        $response = $client->request(get_theme_file_uri('resources/views/index.blade.php'), [
            'method' => 'HEAD',
            'sslverify' => ! $this->app->environment('local', 'testing', 'test', 'dev', 'development'),
            'sslcertificates' => $this->getCertificate()
        ]);

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code >= 200 && $status_code < 300) {
            throw new \Exception('i should not b abl 2 c bladez ya dummy');
        }
    }

    /**
     * Get CA bundle from common system locations.
     *
     * This follows a similar routine as Guzzle with notable
     * fallback to CA bundle provided by WordPress.
     *
     * @return string Path to certificate
     */
    protected function getCertificate() : string
    {
        return ini_get('openssl.cafile')
            ?: ini_get('curl.cainfo')
            ?: openssl_get_cert_locations()['default_cert_file']
            ?? \ABSPATH . \WPINC . '/certificates/ca-bundle.crt';
    }
}
