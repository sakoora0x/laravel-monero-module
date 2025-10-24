<?php

use Illuminate\Support\Facades\Http;
use sakoora0x\LaravelMoneroModule\Api\Api;


it('can create an API instance', function () {
    $api = new Api('localhost', 18081);

    expect($api)->toBeInstanceOf(Api::class);
});

it('can create an API instance with credentials', function () {
    $api = new Api('localhost', 18081, 'username', 'password');

    expect($api)->toBeInstanceOf(Api::class);
});

it('can make RPC requests', function () {
    Http::fake(function ($request) {
        $body = json_decode($request->body(), true);
        return Http::response([
            'id' => $body['id'],
            'jsonrpc' => '2.0',
            'result' => ['height' => 3000000],
        ]);
    });

    $api = new Api('localhost', 18081);
    $result = $api->request('get_height');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('height')
        ->and($result['height'])->toBe(3000000);
});

it('throws exception on request ID mismatch', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'wrong-id',
            'jsonrpc' => '2.0',
            'result' => ['height' => 3000000],
        ]),
    ]);

    $api = new Api('localhost', 18081);

    expect(fn() => $api->request('get_height'))
        ->toThrow(\Exception::class, 'Request ID is not correct');
});

it('throws exception on API error', function () {
    Http::fake(function ($request) {
        $body = json_decode($request->body(), true);
        return Http::response([
            'id' => $body['id'],
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -1,
                'message' => 'Something went wrong',
            ],
        ]);
    });

    $api = new Api('localhost', 18081);

    expect(fn() => $api->request('get_height'))
        ->toThrow(\Exception::class, 'Something went wrong');
});

it('can get wallet height', function () {
    Http::fake(function ($request) {
        $body = json_decode($request->body(), true);
        return Http::response([
            'id' => $body['id'],
            'jsonrpc' => '2.0',
            'result' => ['height' => 2500000],
        ]);
    });

    $api = new Api('localhost', 18081);
    $height = $api->getHeight();

    expect($height)->toBe(2500000);
});

it('can get daemon height', function () {
    Http::fake([
        '*' => Http::response(['height' => 3000000]),
    ]);

    $api = new Api('localhost', 18081, null, null, 'localhost:18082');
    $height = $api->getDaemonHeight();

    expect($height)->toBe(3000000);
});

it('throws exception if height is not in response', function () {
    Http::fake([
        '*' => Http::response([
            'id' => '123',
            'jsonrpc' => '2.0',
            'result' => ['status' => 'OK'],
        ]),
    ]);

    $api = new Api('localhost', 18081);

    expect(fn() => $api->getHeight())
        ->toThrow(\Exception::class);
});

it('uses digest authentication for requests', function () {
    Http::fake(function ($request) {
        $body = json_decode($request->body(), true);
        return Http::response([
            'id' => $body['id'],
            'jsonrpc' => '2.0',
            'result' => ['height' => 3000000],
        ]);
    });

    $api = new Api('localhost', 18081, 'testuser', 'testpass');
    $api->request('get_height');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'json_rpc');
    });
});

it('makes daemon requests without json_rpc', function () {
    Http::fake([
        'http://localhost:18082/get_height' => Http::response(['height' => 3000000]),
    ]);

    $api = new Api('localhost', 18081, null, null, 'localhost:18082');
    $height = $api->getDaemonHeight();

    expect($height)->toBe(3000000);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/get_height')
            && !str_contains($request->url(), 'json_rpc');
    });
});
