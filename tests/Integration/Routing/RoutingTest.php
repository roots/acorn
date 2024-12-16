<?php

namespace Roots\Acorn\Tests\Integration\Routing;

use GuzzleHttp\Client;

uses(RoutingTestCase::class);

it('should return a 200 status code for WordPress homepage', function () {
    $client = new Client([
        'verify' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/');
    expect($response->getStatusCode())->toBe(200);
});

it('should return a 200 status code for Acorn test route', function () {
    $client = new Client([
        'verify' => false,
    ]);

    $response = $client->request('GET', 'http://web:8080/test');
    expect($response->getStatusCode())->toBe(200);
    expect((string) $response->getBody())->toBe('Howdy');
});
