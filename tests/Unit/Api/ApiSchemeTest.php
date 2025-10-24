<?php

use Illuminate\Support\Facades\Http;
use sakoora0x\LaravelMoneroModule\Api\Api;

it('defaults to http scheme', function () {
    Http::fake(function ($request) {
        expect($request->url())->toStartWith('http://localhost:18081');

        $body = json_decode($request->body(), true);
        return Http::response([
            'id' => $body['id'],
            'jsonrpc' => '2.0',
            'result' => ['version' => 196608],
        ]);
    });

    $api = new Api('localhost', 18081);
    $api->getVersion();
});

it('supports https scheme', function () {
    Http::fake(function ($request) {
        expect($request->url())->toStartWith('https://secure.example.com:18081');

        $body = json_decode($request->body(), true);
        return Http::response([
            'id' => $body['id'],
            'jsonrpc' => '2.0',
            'result' => ['version' => 196608],
        ]);
    });

    $api = new Api('secure.example.com', 18081, null, null, null, 'https');
    $api->getVersion();
});

it('falls back to http for invalid scheme', function () {
    Http::fake(function ($request) {
        expect($request->url())->toStartWith('http://localhost:18081');

        $body = json_decode($request->body(), true);
        return Http::response([
            'id' => $body['id'],
            'jsonrpc' => '2.0',
            'result' => ['version' => 196608],
        ]);
    });

    $api = new Api('localhost', 18081, null, null, null, 'ftp');
    $api->getVersion();
});

it('uses scheme for daemon requests', function () {
    Http::fake(function ($request) {
        expect($request->url())->toStartWith('https://daemon.example.com:18082');

        return Http::response(['height' => 3000000]);
    });

    $api = new Api('localhost', 18081, null, null, 'daemon.example.com:18082', 'https');
    $height = $api->getDaemonHeight();

    expect($height)->toBe(3000000);
});

it('preserves scheme in daemon URL if already provided', function () {
    Http::fake(function ($request) {
        expect($request->url())->toStartWith('https://daemon.example.com:18082');

        return Http::response(['height' => 3000000]);
    });

    $api = new Api('localhost', 18081, null, null, 'https://daemon.example.com:18082', 'http');
    $height = $api->getDaemonHeight();

    expect($height)->toBe(3000000);
});

it('creates node with https scheme', function () {
    $node = $this->createNode([
        'scheme' => 'https',
        'host' => 'secure.example.com',
    ]);

    expect($node->scheme)->toBe('https')
        ->and($node->host)->toBe('secure.example.com');
});
