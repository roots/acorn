<?php

namespace Roots\Acorn\Tests\Integration\Routing;

use GuzzleHttp\Client;

uses(RoutingTestCase::class)->group('integration');

expect()->extend('toHaveBodyClass', function (string $class) {
    preg_match('/<body[^>]*class=["\']([^"\']*)["\']/', $this->value, $matches);
    expect($matches)->toHaveCount(2, 'No body tag with class attribute found');
    expect($matches[1])->toContain($class);

    return $this;
});

it('handles the test route', function () {
    $client = new Client([
        'verify' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/test/');
    expect($response->getStatusCode())->toBe(200);
    expect((string) $response->getBody())->toContain('Howdy');
});

it('does not intercept WordPress admin routes', function () {
    $client = new Client([
        'verify' => false,
        'allow_redirects' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/wp/wp-admin/');
    expect($response->getStatusCode())->toBe(302);
    expect($response->getHeader('Location')[0])->toContain('wp-login.php');
});

it('handles non-existent routes with 404', function () {
    $client = new Client([
        'verify' => false,
        'http_errors' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/non-existent-'.time());
    expect($response->getStatusCode())->toBe(404);
    expect((string) $response->getBody())->toContain('Page not found');
    expect((string) $response->getBody())->toHaveBodyClass('error404');
});

it('handles default homepage', function () {
    $client = new Client([
        'verify' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/');
    expect($response->getStatusCode())->toBe(200);
    expect((string) $response->getBody())->toHaveBodyClass('home');
});

it('handles WordPress REST API routes', function () {
    $client = new Client([
        'verify' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/wp-json/');
    expect($response->getStatusCode())->toBe(200);

    $data = json_decode((string) $response->getBody(), true);
    expect($data)->toHaveKey('name');
    expect($data)->toHaveKey('url');
});

it('handles WordPress search route', function () {
    $client = new Client([
        'verify' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/search/test/');
    expect($response->getStatusCode())->toBe(200);
    expect((string) $response->getBody())->toHaveBodyClass('search');
});
